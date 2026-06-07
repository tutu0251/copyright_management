const mongoose = require('mongoose');

const roleSchema = new mongoose.Schema(
  {
    slug: { type: String, required: true, unique: true },
    name: { type: String, required: true },
    description: { type: String, default: '' },
    permissions: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Permission' }],
  },
  { timestamps: true },
);

const Role = mongoose.model('Role', roleSchema);

module.exports = { Role };
