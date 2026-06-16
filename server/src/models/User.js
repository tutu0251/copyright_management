const bcrypt = require('bcryptjs');
const { createModel } = require('../lib/model.js');

const User = createModel('User', {
  collection: 'users',
  timestamps: true,
  fields: {
    email: { lowercase: true, trim: true },
    passwordHash: {},
    displayName: {},
    isActive: { default: true },
    roles: { ref: 'Role', array: true },
    lastLoginAt: {},
  },
  methods: {
    verifyPassword(plain) {
      return bcrypt.compare(plain, this.passwordHash);
    },
  },
  statics: {
    hashPassword(plain) {
      return bcrypt.hash(plain, 12);
    },
  },
});

module.exports = { User };
