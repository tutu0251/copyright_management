const { createModel } = require('../lib/model.js');

// Metadata record for a binary asset (image / document / video / audio) stored
// in GridFS. The actual bytes live in the `assets.*` GridFS collections; this
// document links the blob (`fileId`) to a Work and carries display metadata.
const Asset = createModel('Asset', {
  collection: 'work_assets',
  timestamps: true,
  fields: {
    work: { ref: 'Work' },
    fileId: {}, // GridFS file _id (ObjectId); not populated, kept as-is.
    filename: {},
    contentType: { default: 'application/octet-stream' },
    size: { default: 0 },
    uploadedBy: { ref: 'User' },
    deletedAt: { default: null },
  },
});

module.exports = { Asset };
