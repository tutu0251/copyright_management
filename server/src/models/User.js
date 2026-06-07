const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

const userSchema = new mongoose.Schema(
  {
    email: { type: String, required: true, unique: true, lowercase: true, trim: true },
    passwordHash: { type: String, required: true },
    displayName: { type: String, required: true },
    isActive: { type: Boolean, default: true },
    roles: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Role' }],
    lastLoginAt: { type: Date },
  },
  { timestamps: true },
);

userSchema.methods.verifyPassword = function (plain) {
  return bcrypt.compare(plain, this.passwordHash);
};

userSchema.statics.hashPassword = function (plain) {
  return bcrypt.hash(plain, 12);
};

const User = mongoose.model('User', userSchema);

module.exports = { User };
