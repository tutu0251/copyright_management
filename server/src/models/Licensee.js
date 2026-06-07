import mongoose from 'mongoose';

const licenseeSchema = new mongoose.Schema(
  {
    name: { type: String, required: true },
    contactEmail: { type: String, default: '' },
    organization: { type: String, default: '' },
    notes: { type: String, default: '' },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

export const Licensee = mongoose.model('Licensee', licenseeSchema);
