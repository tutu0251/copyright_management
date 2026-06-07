import mongoose from 'mongoose';

const workSchema = new mongoose.Schema(
  {
    title: { type: String, required: true },
    slug: { type: String, sparse: true },
    workType: { type: String, default: 'Text' },
    creator: { type: String, default: '' },
    owner: { type: String, default: '' },
    copyrightStatus: { type: String, default: 'draft' },
    riskLevel: { type: String, enum: ['Low', 'Medium', 'High'], default: 'Low' },
    description: { type: String, default: '' },
    registeredAt: { type: Date },
    createdBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

workSchema.index({ deletedAt: 1 });
workSchema.index({ workType: 1 });

export const Work = mongoose.model('Work', workSchema);
