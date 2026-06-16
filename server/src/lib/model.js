// Lightweight data-access layer over the native mongodb@3.5.x driver.
//
// The application was originally written against the Mongoose ODM. Rather than
// rewrite every route to issue raw driver calls, this module reproduces the
// small slice of the Mongoose Model API that the codebase actually uses:
//
//   Model.find(q) / findOne(q) / findById(id)   -> chainable, thenable query
//     .sort() .limit() .select() .populate() .lean()
//   Model.create(doc)                            -> inserts, returns a Document
//   Model.findOneAndUpdate / findByIdAndUpdate   -> { new: true } returns updated
//   Model.updateOne(filter, update, { upsert })  -> $set / $setOnInsert aware
//   Model.countDocuments / distinct / aggregate
//   schema.methods (instance) + schema.statics
//
// It also handles the conveniences Mongoose gave us for free: automatic
// createdAt/updatedAt timestamps, schema defaults, lowercase/trim string
// transforms, ObjectId casting for `_id` and declared ref fields, and
// `.populate()` (including nested populate and field projections).

const mongodb = require('mongodb');
const { getDb } = require('../config/db.js');

const ObjectId = mongodb.ObjectId || mongodb.ObjectID;

// Registry of model name -> Model, used to resolve `ref` targets for populate.
const registry = new Map();

// Sentinel returned by query-casting when a filter can never match (e.g. an
// `_id` string that is not a valid ObjectId). Lets reads short-circuit to an
// empty result instead of throwing, mirroring a not-found lookup.
const IMPOSSIBLE = Symbol('impossible-query');

function isPlainObject(value) {
  return (
    value != null &&
    typeof value === 'object' &&
    !Array.isArray(value) &&
    !(value instanceof ObjectId) &&
    !(value instanceof Date)
  );
}

// Cast a single value destined for an `_id`/ref field in a *query filter*.
// Returns IMPOSSIBLE for a bare string that cannot be a valid ObjectId.
function castIdQueryValue(value) {
  if (value == null) return value;
  if (value instanceof ObjectId) return value;
  if (Array.isArray(value)) return value.map(castIdQueryValue);
  if (typeof value === 'string') {
    return ObjectId.isValid(value) ? new ObjectId(value) : IMPOSSIBLE;
  }
  if (isPlainObject(value)) {
    const out = {};
    for (const key of Object.keys(value)) {
      if (key === '$in' || key === '$nin') {
        out[key] = (value[key] || []).map(castIdWriteValue);
      } else if (key === '$eq' || key === '$ne') {
        out[key] = castIdWriteValue(value[key]);
      } else {
        out[key] = value[key];
      }
    }
    return out;
  }
  return value;
}

// Cast a value destined for a write (create/$set). Unlike the query variant it
// never returns IMPOSSIBLE — an unparseable string is left untouched.
function castIdWriteValue(value) {
  if (value == null) return value;
  if (value instanceof ObjectId) return value;
  if (Array.isArray(value)) return value.map(castIdWriteValue);
  if (typeof value === 'string' && ObjectId.isValid(value)) return new ObjectId(value);
  return value;
}

// Recursively cast `_id` and declared ref fields within a query filter.
function castQuery(query, refFields) {
  if (!isPlainObject(query)) return query;
  const out = {};
  for (const key of Object.keys(query)) {
    const value = query[key];
    if (key === '$or' || key === '$and' || key === '$nor') {
      out[key] = (value || []).map((sub) => {
        const cast = castQuery(sub, refFields);
        // An impossible sub-clause becomes a never-match condition.
        return cast === IMPOSSIBLE ? { _id: { $exists: false }, _impossible: true } : cast;
      });
    } else if (key === '_id' || refFields.has(key)) {
      const cast = castIdQueryValue(value);
      if (cast === IMPOSSIBLE) return IMPOSSIBLE;
      out[key] = cast;
    } else {
      out[key] = value;
    }
  }
  return out;
}

function plainCopy(doc) {
  return Object.assign({}, doc);
}

// Normalize a `.select('a b')` / `.populate(path, 'a b')` argument into a
// mongodb projection object, or undefined.
function toProjection(select) {
  if (!select) return undefined;
  if (typeof select === 'string') {
    const proj = {};
    for (const field of select.trim().split(/\s+/)) {
      if (field) proj[field] = 1;
    }
    return proj;
  }
  if (isPlainObject(select)) return select;
  return undefined;
}

// Normalize the many shapes of `.populate()` into an array of specs:
//   { path, select?, populate? }
function normalizePopulate(path, select) {
  if (path && typeof path === 'object' && !Array.isArray(path)) {
    const spec = { path: path.path, select: toProjection(path.select) };
    if (path.populate) spec.populate = normalizePopulate(path.populate);
    return [spec];
  }
  if (typeof path === 'string') {
    const proj = toProjection(select);
    return path
      .trim()
      .split(/\s+/)
      .filter(Boolean)
      .map((p) => ({ path: p, select: proj }));
  }
  return [];
}

function createModel(name, schema = {}) {
  const collectionName = schema.collection || `${name.toLowerCase()}s`;
  const fields = schema.fields || {};
  const refFields = new Set(Object.keys(fields).filter((f) => fields[f].ref));
  const timestamps = !!schema.timestamps;

  function col() {
    return getDb().collection(collectionName);
  }

  class Document {
    constructor(data) {
      Object.assign(this, data);
    }

    toObject() {
      return plainCopy(this);
    }

    async save() {
      const data = plainCopy(this);
      const id = data._id;
      delete data._id;
      if (timestamps) {
        data.updatedAt = new Date();
        this.updatedAt = data.updatedAt;
      }
      await col().updateOne({ _id: id }, { $set: data });
      return this;
    }
  }

  for (const methodName of Object.keys(schema.methods || {})) {
    Object.defineProperty(Document.prototype, methodName, {
      value: schema.methods[methodName],
      enumerable: false,
      writable: true,
      configurable: true,
    });
  }

  function hydrate(data) {
    return data == null ? null : new Document(data);
  }

  // --- defaults / transforms applied on insert ---------------------------
  function prepareForInsert(input) {
    const doc = Object.assign({}, input);
    for (const field of Object.keys(fields)) {
      const def = fields[field];
      if (doc[field] === undefined && 'default' in def) {
        doc[field] = typeof def.default === 'function' ? def.default() : def.default;
      }
      if (typeof doc[field] === 'string') {
        if (def.trim) doc[field] = doc[field].trim();
        if (def.lowercase) doc[field] = doc[field].toLowerCase();
      }
      if (def.ref && doc[field] != null) {
        doc[field] = castIdWriteValue(doc[field]);
      }
    }
    if (timestamps) {
      const now = new Date();
      if (doc.createdAt === undefined) doc.createdAt = now;
      if (doc.updatedAt === undefined) doc.updatedAt = now;
    }
    return doc;
  }

  // Cast ref fields inside a $set / $setOnInsert payload.
  function castWriteObject(obj) {
    if (!isPlainObject(obj)) return obj;
    const out = Object.assign({}, obj);
    for (const field of Object.keys(out)) {
      if (refFields.has(field) && out[field] != null) {
        out[field] = castIdWriteValue(out[field]);
      } else if (typeof out[field] === 'string') {
        const def = fields[field];
        if (def && def.trim) out[field] = out[field].trim();
        if (def && def.lowercase) out[field] = out[field].toLowerCase();
      }
    }
    return out;
  }

  function prepareUpdate(update, opts) {
    const upsert = !!(opts && opts.upsert);
    const hasOperators = isPlainObject(update) && Object.keys(update).some((k) => k.startsWith('$'));
    let u;
    if (hasOperators) {
      u = Object.assign({}, update);
      if (u.$set) u.$set = castWriteObject(u.$set);
      if (u.$setOnInsert) u.$setOnInsert = castWriteObject(u.$setOnInsert);
    } else {
      u = { $set: castWriteObject(update) };
    }
    if (timestamps) {
      const now = new Date();
      // A "real" update touches more than just the insert-only payload.
      const realUpdate = Object.keys(u).some((k) => k !== '$setOnInsert');
      if (realUpdate) {
        u.$set = u.$set || {};
        if (!('updatedAt' in u.$set)) u.$set.updatedAt = now;
      }
      if (upsert) {
        u.$setOnInsert = u.$setOnInsert || {};
        if (!('createdAt' in u.$setOnInsert)) u.$setOnInsert.createdAt = now;
        const setHasUpdated = u.$set && 'updatedAt' in u.$set;
        if (!setHasUpdated && !('updatedAt' in u.$setOnInsert)) {
          u.$setOnInsert.updatedAt = now;
        }
      }
    }
    return u;
  }

  // --- populate ----------------------------------------------------------
  // Fetch raw (plain-object) documents for populate resolution.
  async function fetchRaw(filter, projection) {
    const f = castQuery(filter, refFields);
    if (f === IMPOSSIBLE) return [];
    let cursor = col().find(f);
    if (projection) cursor = cursor.project(projection);
    return cursor.toArray();
  }

  // Resolve populate specs against an array of plain documents (mutates them).
  async function populate(docs, specs) {
    if (!specs || !specs.length || !docs.length) return docs;
    for (const spec of specs) {
      const def = fields[spec.path];
      if (!def || !def.ref) continue;
      const RefModel = registry.get(def.ref);
      if (!RefModel) continue;
      const isArray = !!def.array;

      const ids = [];
      for (const doc of docs) {
        const value = doc && doc[spec.path];
        if (value == null) continue;
        if (isArray) {
          for (const item of value) if (item != null) ids.push(item);
        } else {
          ids.push(value);
        }
      }

      let refDocs = [];
      if (ids.length) {
        refDocs = await RefModel.__fetchRaw({ _id: { $in: ids } }, spec.select);
        if (spec.populate) await RefModel.__populate(refDocs, spec.populate);
      }
      const byId = new Map(refDocs.map((rd) => [String(rd._id), rd]));

      for (const doc of docs) {
        if (!doc) continue;
        const value = doc[spec.path];
        if (value == null) {
          doc[spec.path] = isArray ? [] : null;
        } else if (isArray) {
          doc[spec.path] = value.map((item) => byId.get(String(item))).filter(Boolean);
        } else {
          doc[spec.path] = byId.get(String(value)) || null;
        }
      }
    }
    return docs;
  }

  // --- query builder -----------------------------------------------------
  function makeQuery(mode, filter) {
    const state = {
      sort: null,
      limit: null,
      projection: null,
      populate: [],
      lean: false,
    };

    async function exec() {
      const f = castQuery(filter || {}, refFields);
      if (f === IMPOSSIBLE) return mode === 'find' ? [] : null;

      if (mode === 'find') {
        let cursor = col().find(f);
        if (state.projection) cursor = cursor.project(state.projection);
        if (state.sort) cursor = cursor.sort(state.sort);
        if (state.limit != null) cursor = cursor.limit(state.limit);
        let docs = await cursor.toArray();
        await populate(docs, state.populate);
        return state.lean ? docs : docs.map(hydrate);
      }

      const options = state.projection ? { projection: state.projection } : {};
      const doc = await col().findOne(f, options);
      if (!doc) return null;
      await populate([doc], state.populate);
      return state.lean ? doc : hydrate(doc);
    }

    const query = {
      sort(value) {
        state.sort = value;
        return query;
      },
      limit(value) {
        state.limit = value;
        return query;
      },
      select(value) {
        state.projection = toProjection(value);
        return query;
      },
      populate(path, select) {
        state.populate.push(...normalizePopulate(path, select));
        return query;
      },
      lean() {
        state.lean = true;
        return query;
      },
      then(resolve, reject) {
        return exec().then(resolve, reject);
      },
      catch(reject) {
        return exec().catch(reject);
      },
      exec,
    };
    return query;
  }

  const Model = {
    modelName: name,
    collectionName,

    find(filter) {
      return makeQuery('find', filter);
    },
    findOne(filter) {
      return makeQuery('findOne', filter);
    },
    findById(id) {
      return makeQuery('findOne', { _id: id });
    },

    async create(input) {
      const doc = prepareForInsert(input);
      const result = await col().insertOne(doc);
      const saved =
        result.ops && result.ops[0]
          ? result.ops[0]
          : Object.assign({ _id: result.insertedId }, doc);
      return hydrate(saved);
    },

    async findOneAndUpdate(filter, update, opts = {}) {
      const f = castQuery(filter, refFields);
      if (f === IMPOSSIBLE) return null;
      const u = prepareUpdate(update, opts);
      const result = await col().findOneAndUpdate(f, u, {
        // mongodb@3.5.x uses `returnOriginal` (renamed to returnDocument in 3.6+).
        returnOriginal: !opts.new,
        upsert: !!opts.upsert,
      });
      return result.value ? hydrate(result.value) : null;
    },

    findByIdAndUpdate(id, update, opts) {
      return Model.findOneAndUpdate({ _id: id }, update, opts);
    },

    async updateOne(filter, update, opts = {}) {
      const f = castQuery(filter, refFields);
      if (f === IMPOSSIBLE) return { matchedCount: 0, modifiedCount: 0 };
      const u = prepareUpdate(update, opts);
      return col().updateOne(f, u, { upsert: !!opts.upsert });
    },

    async countDocuments(filter = {}) {
      const f = castQuery(filter, refFields);
      if (f === IMPOSSIBLE) return 0;
      return col().countDocuments(f);
    },

    async distinct(field, filter = {}) {
      const f = castQuery(filter, refFields);
      if (f === IMPOSSIBLE) return [];
      return col().distinct(field, f);
    },

    async aggregate(pipeline) {
      return col().aggregate(pipeline).toArray();
    },

    // Internal hooks used by populate across models.
    __fetchRaw: fetchRaw,
    __populate: populate,
  };

  for (const staticName of Object.keys(schema.statics || {})) {
    Model[staticName] = schema.statics[staticName];
  }

  registry.set(name, Model);
  return Model;
}

module.exports = { createModel, ObjectId, registry };
