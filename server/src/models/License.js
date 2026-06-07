import mongoose from 'mongoose';

const licenseSchema = new mongoose.Schema(
  {
    work: { type: mongoose.Schema.Types.ObjectId, ref: 'Work', required: true },
    licensee: { type: mongoose.Schema.Types.ObjectId, ref: 'Licensee', required: true },
    licenseType: { type: String, default: 'non_exclusive' },
    licenseStatus: { type: String, default: 'draft' },
    paymentStatus: { type: String, default: 'unpaid' },
    feeAmount: { type: Number, default: 0 },
    territory: { type: String, default: '' },
    startDate: { type: Date },
    endDate: { type: Date },
    deletedAt: { type: Date, default: null },
  },
  { timestamps: true },
);

export const License = mongoose.model('License', licenseSchema);
