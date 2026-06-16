-- Copyright Management — seed data (idempotent).
-- Reproduces server/src/scripts/seed.js: permissions, the four roles with their
-- exact preset grants, the admin user, and the sample catalog/licensing rows.
--
--   mysql -u root copyright_management < sql/seed.sql
--
-- Admin login after seeding: admin@example.com / Admin123!
-- (Change this password in any shared environment.)

SET @now = NOW();

-- ---------------------------------------------------------------------------
-- Permissions (slug is unique; INSERT IGNORE keeps this re-runnable)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO permissions (slug, name, description, created_at, updated_at) VALUES
 ('works.view','View works','Browse and open work records',@now,@now),
 ('works.create','Create works','Register new works',@now,@now),
 ('works.update','Update works','Edit work metadata',@now,@now),
 ('works.delete','Delete works','Remove work records',@now,@now),
 ('owners.view','View owners','Browse owner directory',@now,@now),
 ('owners.create','Create owners','Add owner records',@now,@now),
 ('owners.update','Update owners','Edit owners and work links',@now,@now),
 ('owners.delete','Delete owners','Remove owner records',@now,@now),
 ('licensees.view','View licensees','Browse licensee directory',@now,@now),
 ('licensees.create','Create licensees','Add licensee records',@now,@now),
 ('licensees.update','Update licensees','Edit licensee records',@now,@now),
 ('licensees.delete','Delete licensees','Remove licensee records',@now,@now),
 ('licenses.view','View licenses','Browse license agreements',@now,@now),
 ('licenses.create','Create licenses','Record new licenses',@now,@now),
 ('licenses.update','Update licenses','Edit license terms',@now,@now),
 ('licenses.delete','Delete licenses','Remove license records',@now,@now),
 ('usage_reports.view','View usage reports','Open monitoring reports',@now,@now),
 ('usage_reports.create','Create usage reports','Log new usage findings',@now,@now),
 ('usage_reports.update','Update usage reports','Edit reports and disposition',@now,@now),
 ('usage_reports.delete','Delete usage reports','Remove usage reports',@now,@now),
 ('cases.view','View cases','Browse infringement cases',@now,@now),
 ('cases.create','Create cases','Open new cases',@now,@now),
 ('cases.update','Update cases','Edit case details',@now,@now),
 ('cases.delete','Delete cases','Remove case records',@now,@now),
 ('cases.status_update','Change case status','Move cases through workflow',@now,@now),
 ('dashboard.view','View dashboard','Access analytics dashboard',@now,@now),
 ('reports.view','View reports','Access analytics reports',@now,@now),
 ('activities.view','View activity log','Read audit activity feed',@now,@now),
 ('settings.manage','Manage settings','Configure roles and permissions',@now,@now),
 ('users.manage','Manage users','Create and deactivate user accounts',@now,@now);

-- ---------------------------------------------------------------------------
-- Roles
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO roles (slug, name, description, created_at, updated_at) VALUES
 ('admin','Administrator','Full system access',@now,@now),
 ('manager','Manager','Create and update catalog, licensing, and cases',@now,@now),
 ('editor','Editor','Manage works and licensing records',@now,@now),
 ('viewer','Viewer','Read-only access',@now,@now);

-- ---------------------------------------------------------------------------
-- Role -> permission grants (INSERT IGNORE on the (role_id,permission_id) PK)
-- ---------------------------------------------------------------------------
-- admin: every permission
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r JOIN permissions p WHERE r.slug = 'admin';

-- manager + editor share the same preset
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r JOIN permissions p
  WHERE r.slug IN ('manager','editor')
    AND p.slug IN (
      'works.view','works.create','works.update',
      'owners.view','owners.create','owners.update',
      'licensees.view','licensees.create','licensees.update',
      'licenses.view','licenses.create','licenses.update',
      'usage_reports.view','usage_reports.create','usage_reports.update',
      'cases.view','cases.create','cases.update','cases.status_update',
      'dashboard.view','reports.view','activities.view');

-- viewer: every *.view plus dashboard/reports/activities
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r JOIN permissions p
  WHERE r.slug = 'viewer'
    AND p.slug IN (
      'works.view','owners.view','licensees.view','licenses.view',
      'usage_reports.view','cases.view',
      'dashboard.view','reports.view','activities.view');

-- ---------------------------------------------------------------------------
-- Admin user (bcrypt hash of 'Admin123!'; portable $2y$ form)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO users (email, password_hash, display_name, is_active, created_at, updated_at)
VALUES ('admin@example.com',
        '$2y$10$jboiUTIy3UZZuRhXqhEgzuRU9qc7pPL6vf.KLejuJp8MMcGncUBPm',
        'System Admin', 1, @now, @now);

INSERT IGNORE INTO user_roles (user_id, role_id)
  SELECT u.id, r.id FROM users u JOIN roles r
  WHERE u.email = 'admin@example.com' AND r.slug = 'admin';

-- ---------------------------------------------------------------------------
-- Sample catalog / licensing data (each guarded so re-running won't duplicate)
-- ---------------------------------------------------------------------------
INSERT INTO works (title, work_type, creator, owner, copyright_status, risk_level, registered_at, created_by, created_at, updated_at)
  SELECT 'Aurora Fields — Stock Photo Pack','Image','Jamie Chen','Studio North LLC','registered','Low','2025-09-01',
         (SELECT id FROM users WHERE email='admin@example.com'),@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM works WHERE title='Aurora Fields — Stock Photo Pack');

INSERT INTO works (title, work_type, creator, owner, copyright_status, risk_level, registered_at, created_by, created_at, updated_at)
  SELECT 'Midnight Choir — Master Recording','Audio','Riley Ortiz','Echo Lane Music','registered','Medium','2025-10-02',
         (SELECT id FROM users WHERE email='admin@example.com'),@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM works WHERE title='Midnight Choir — Master Recording');

INSERT INTO works (title, work_type, creator, owner, copyright_status, risk_level, registered_at, created_by, created_at, updated_at)
  SELECT 'Meridian SaaS — Onboarding Video','Video','Sam Okonkwo','Pixel Harbor Inc.','registered','High','2025-11-03',
         (SELECT id FROM users WHERE email='admin@example.com'),@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM works WHERE title='Meridian SaaS — Onboarding Video');

INSERT INTO works (title, work_type, creator, owner, copyright_status, risk_level, registered_at, created_by, created_at, updated_at)
  SELECT 'Policy Handbook 2026 (Internal)','Text','Jamie Chen','Studio North LLC','registered','Low','2025-12-04',
         (SELECT id FROM users WHERE email='admin@example.com'),@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM works WHERE title='Policy Handbook 2026 (Internal)');

INSERT INTO works (title, work_type, creator, owner, copyright_status, risk_level, registered_at, created_by, created_at, updated_at)
  SELECT 'LedgerFlow — Mobile App UI','Software','Riley Ortiz','Echo Lane Music','registered','Medium','2025-09-05',
         (SELECT id FROM users WHERE email='admin@example.com'),@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM works WHERE title='LedgerFlow — Mobile App UI');

INSERT INTO owners (legal_name, entity_type, email, created_at, updated_at)
  SELECT 'Studio North LLC','company','legal@studionorth.example',@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM owners WHERE legal_name='Studio North LLC');

INSERT INTO licensees (name, contact_email, organization, created_at, updated_at)
  SELECT 'Brightfield Media','licensing@brightfield.example','Brightfield Media Group',@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM licensees WHERE name='Brightfield Media');

INSERT INTO licenses (work_id, licensee_id, license_type, license_status, payment_status, fee_amount, start_date, end_date, created_at, updated_at)
  SELECT (SELECT id FROM works WHERE title='Aurora Fields — Stock Photo Pack'),
         (SELECT id FROM licensees WHERE name='Brightfield Media'),
         'non_exclusive','active','paid',2500.00,'2025-01-01','2026-12-31',@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM licenses WHERE fee_amount=2500.00
         AND work_id=(SELECT id FROM works WHERE title='Aurora Fields — Stock Photo Pack'));

INSERT INTO usage_reports (work_id, usage_type, detected_source, detected_at, created_at, updated_at)
  SELECT (SELECT id FROM works WHERE title='Midnight Choir — Master Recording'),
         'suspected','Social crawl',@now,@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM usage_reports WHERE detected_source='Social crawl');

INSERT INTO infringement_cases (title, work_id, case_status, priority, description, created_at, updated_at)
  SELECT 'Unauthorized stream — Midnight Choir',
         (SELECT id FROM works WHERE title='Midnight Choir — Master Recording'),
         'investigating','high','Detected on third-party platform.',@now,@now
  FROM dual WHERE NOT EXISTS (SELECT 1 FROM infringement_cases WHERE title='Unauthorized stream — Midnight Choir');
