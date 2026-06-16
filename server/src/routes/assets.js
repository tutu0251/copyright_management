const { Router } = require('express');
const { Asset } = require('../models/Asset.js');
const { Work } = require('../models/Work.js');
const { getBucket } = require('../lib/gridfs.js');
const { requireAuth, requirePermission } = require('../middleware/auth.js');

function formatAsset(d) {
  return {
    id: d._id.toString(),
    work_id: d.work ? d.work.toString() : null,
    filename: d.filename,
    content_type: d.contentType,
    contentType: d.contentType,
    size: d.size,
    created_at: d.createdAt,
  };
}

function safeFilename(name) {
  // Strip characters that would break a Content-Disposition header.
  return String(name || 'file').replace(/[\r\n"]/g, '_');
}

// GET /api/works/:id/assets  — list a work's assets (works.view)
function listWorkAssets(req, res) {
  Asset.find({ work: req.params.id, deletedAt: null })
    .sort({ createdAt: -1 })
    .lean()
    .then(function (items) {
      res.json({ items: items.map(formatAsset) });
    })
    .catch(function () {
      res.status(500).json({ error: 'Failed to list assets' });
    });
}

// POST /api/works/:id/assets  — stream a raw binary body into GridFS (works.update).
// The client sends the file as the request body with Content-Type
// application/octet-stream (so body-parser.json never consumes it), and passes
// the real name/type via X-File-Name / X-Content-Type headers.
function uploadWorkAsset(req, res) {
  var workId = req.params.id;
  Work.findOne({ _id: workId, deletedAt: null })
    .then(function (work) {
      if (!work) {
        res.status(404).json({ error: 'Work not found' });
        return;
      }
      var rawName = req.headers['x-file-name']
        ? decodeURIComponent(req.headers['x-file-name'])
        : (req.query.filename || 'upload.bin');
      var contentType = req.headers['x-content-type'] || req.headers['content-type'] || 'application/octet-stream';

      var bucket = getBucket();
      var uploadStream = bucket.openUploadStream(rawName, { contentType: contentType });
      var failed = false;

      uploadStream.on('error', function () {
        failed = true;
        if (!res.headersSent) res.status(500).json({ error: 'Upload failed' });
      });
      req.on('error', function () {
        try { uploadStream.abort(function () {}); } catch (e) { /* ignore */ }
      });
      uploadStream.on('finish', function (file) {
        if (failed) return;
        var size = file && file.length != null ? file.length : uploadStream.length;
        Asset.create({
          work: workId,
          fileId: uploadStream.id,
          filename: rawName,
          contentType: contentType,
          size: size,
          uploadedBy: req.user._id,
        })
          .then(function (doc) {
            res.status(201).json({ item: formatAsset(doc.toObject()) });
          })
          .catch(function () {
            res.status(500).json({ error: 'Failed to record asset' });
          });
      });

      req.pipe(uploadStream);
    })
    .catch(function () {
      res.status(500).json({ error: 'Server error' });
    });
}

// GET /api/assets/:id  — stream bytes back for preview (inline) or download
// (?download=1). Supports HTTP Range so video/audio can seek (works.view).
function downloadAsset(req, res) {
  Asset.findOne({ _id: req.params.id, deletedAt: null })
    .then(function (asset) {
      if (!asset) {
        res.status(404).json({ error: 'Not found' });
        return;
      }
      var bucket = getBucket();
      var total = asset.size || 0;
      var contentType = asset.contentType || 'application/octet-stream';
      var dispType = req.query.download ? 'attachment' : 'inline';
      res.setHeader('Content-Type', contentType);
      res.setHeader('Content-Disposition', dispType + '; filename="' + safeFilename(asset.filename) + '"');
      res.setHeader('Accept-Ranges', 'bytes');

      var range = req.headers.range;
      var match = range && total ? /bytes=(\d*)-(\d*)/.exec(range) : null;
      if (match) {
        var start = match[1] ? parseInt(match[1], 10) : 0;
        var end = match[2] ? parseInt(match[2], 10) : total - 1;
        if (isNaN(start)) start = 0;
        if (isNaN(end) || end >= total) end = total - 1;
        if (start > end || start >= total) {
          res.setHeader('Content-Range', 'bytes */' + total);
          res.status(416).end();
          return;
        }
        res.status(206);
        res.setHeader('Content-Range', 'bytes ' + start + '-' + end + '/' + total);
        res.setHeader('Content-Length', end - start + 1);
        // GridFS `end` is exclusive, so add 1 to include the final byte.
        var partial = bucket.openDownloadStream(asset.fileId, { start: start, end: end + 1 });
        partial.on('error', function () { res.end(); });
        partial.pipe(res);
        return;
      }

      if (total) res.setHeader('Content-Length', total);
      var stream = bucket.openDownloadStream(asset.fileId);
      stream.on('error', function () {
        if (!res.headersSent) res.status(404).json({ error: 'File missing' });
        else res.end();
      });
      stream.pipe(res);
    })
    .catch(function () {
      if (!res.headersSent) res.status(500).json({ error: 'Server error' });
    });
}

// DELETE /api/assets/:id  — remove the blob from GridFS and soft-delete the
// metadata record (works.update).
function deleteAsset(req, res) {
  Asset.findOne({ _id: req.params.id, deletedAt: null })
    .then(function (asset) {
      if (!asset) {
        res.status(404).json({ error: 'Not found' });
        return;
      }
      var bucket = getBucket();
      bucket.delete(asset.fileId, function () {
        // Soft-delete metadata regardless of whether the blob was already gone.
        Asset.findByIdAndUpdate(asset._id, { $set: { deletedAt: new Date() } }, { new: true })
          .then(function () { res.json({ ok: true }); })
          .catch(function () { res.status(500).json({ error: 'Failed to remove asset' }); });
      });
    })
    .catch(function () {
      res.status(500).json({ error: 'Server error' });
    });
}

const assetsRouter = Router();
assetsRouter.get('/:id', requireAuth, requirePermission('works.view'), downloadAsset);
assetsRouter.delete('/:id', requireAuth, requirePermission('works.update'), deleteAsset);

module.exports = { assetsRouter, listWorkAssets, uploadWorkAsset };
