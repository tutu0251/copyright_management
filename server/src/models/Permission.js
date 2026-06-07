import mongoose from 'mongoose';

const permissionSchema = new mongoose.Schema(
  {
    slug: { type: String, required: true, unique: true },
    name: { type: String, required: true },
    description: { type: String, default: '' },
  },
  { timestamps: true },
);

export const Permission = mongoose.model('Permission', permissionSchema);
