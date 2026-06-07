const mongoose = require('mongoose');

const usageReportSchema = new mongoose.Schema(
  {
    work: { type: mongoose.Schema.Types.ObjectId, ref: 'Work', required: true },
    usageType: { type: String, default: 'suspected' },
    detectedSource: { type: String, default: '' },
    detectedAt: { type: Date, default: Date.now },
    notes: { type: String, default: '' },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

const UsageReport = mongoose.model('UsageReport', usageReportSchema);

module.exports = { UsageReport };
