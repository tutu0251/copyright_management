import mongoose from 'mongoose';

const workOwnerSchema = new mongoose.Schema(
  {
    work: { type: mongoose.Schema.Types.ObjectId, ref: 'Work', required: true },
    owner: { type: mongoose.Schema.Types.ObjectId, ref: 'Owner', required: true },
    sharePercent: { type: Number, default: 100 },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

workOwnerSchema.index({ work: 1, owner: 1 });

export const WorkOwner = mongoose.model('WorkOwner', workOwnerSchema);
