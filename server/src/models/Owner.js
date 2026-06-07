import mongoose from 'mongoose';

const ownerSchema = new mongoose.Schema(
  {
    legalName: { type: String, required: true },
    entityType: { type: String, default: 'individual' },
    email: { type: String, default: '' },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

export const Owner = mongoose.model('Owner', ownerSchema);
