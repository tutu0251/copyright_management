import mongoose from 'mongoose';

const caseNoteSchema = new mongoose.Schema({
  body: { type: String, required: true },
  author: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  createdAt: { type: Date, default: Date.now },
});

const caseSchema = new mongoose.Schema(
  {
    title: { type: String, required: true },
    work: { type: mongoose.Schema.Types.ObjectId, ref: 'Work' },
    usageReport: { type: mongoose.Schema.Types.ObjectId, ref: 'UsageReport' },
    caseStatus: { type: String, default: 'open' },
    priority: { type: String, default: 'medium' },
    description: { type: String, default: '' },
    notes: [caseNoteSchema],
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

export const InfringementCase = mongoose.model('InfringementCase', caseSchema);
