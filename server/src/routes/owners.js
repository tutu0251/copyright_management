import { createCrudRoutes } from './crudFactory.js';
import { Owner } from '../models/Owner.js';

export default createCrudRoutes({
  Model: Owner,
  viewPerm: 'owners.view',
  createPerm: 'owners.create',
  updatePerm: 'owners.update',
  deletePerm: 'owners.delete',
  formatDoc: (d) => ({
    id: d._id.toString(),
    legal_name: d.legalName,
    legalName: d.legalName,
    entity_type: d.entityType,
    entityType: d.entityType,
    email: d.email,
  }),
  beforeCreate: (body) => ({
    legalName: body.legalName || body.legal_name,
    entityType: body.entityType || body.entity_type || 'individual',
    email: body.email || '',
  }),
});
