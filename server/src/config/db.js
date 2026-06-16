const mongodb = require('mongodb');

const MongoClient = mongodb.MongoClient;

let client = null;
let db = null;

async function connectDb() {
  const uri = process.env.MONGODB_URI || 'mongodb://127.0.0.1:27017/copyright_management';
  // mongodb@3.5.x: opt into the new URL parser and unified topology to avoid the
  // legacy-driver deprecation warnings. The database name is taken from the URI.
  client = await MongoClient.connect(uri, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
  db = client.db();
  console.log('MongoDB connected');
  return db;
}

function getDb() {
  if (!db) {
    throw new Error('Database not connected. Call connectDb() before querying.');
  }
  return db;
}

async function closeDb() {
  if (client) {
    await client.close();
  }
  client = null;
  db = null;
}

module.exports = { connectDb, getDb, closeDb };
