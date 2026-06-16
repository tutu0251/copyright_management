// GridFS access for binary asset storage (images, docs, videos).
//
// The native mongodb@3.5.x driver ships GridFSBucket, so no extra dependency is
// needed. Files are stored in the `assets.files` / `assets.chunks` collections;
// the application keeps a parallel metadata document (see models/Asset.js) that
// links each stored blob to a Work.

const mongodb = require('mongodb');
const { getDb } = require('../config/db.js');

const ObjectId = mongodb.ObjectId || mongodb.ObjectID;

let bucket = null;

function getBucket() {
  if (!bucket) {
    bucket = new mongodb.GridFSBucket(getDb(), { bucketName: 'assets' });
  }
  return bucket;
}

module.exports = { getBucket, ObjectId };
