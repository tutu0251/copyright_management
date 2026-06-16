-- Copyright Management — MySQL 5.x schema (InnoDB, utf8)
-- Ported from the MongoDB collections; embedded arrays normalized into junction
-- and child tables, ObjectIds replaced by integer AUTO_INCREMENT primary keys,
-- and `deletedAt` mapped to nullable `deleted_at` for soft deletes.
--
--   mysql -u root copyright_management < sql/schema.sql
--
-- (create the database first: CREATE DATABASE copyright_management
--  CHARACTER SET utf8 COLLATE utf8_general_ci;)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS work_assets;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS case_notes;
DROP TABLE IF EXISTS infringement_cases;
DROP TABLE IF EXISTS usage_reports;
DROP TABLE IF EXISTS licenses;
DROP TABLE IF EXISTS licensees;
DROP TABLE IF EXISTS work_owners;
DROP TABLE IF EXISTS owners;
DROP TABLE IF EXISTS works;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Identity & access
-- ---------------------------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email           VARCHAR(255) NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  display_name    VARCHAR(255) NOT NULL,
  is_active       TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at   DATETIME NULL,
  created_at      DATETIME NOT NULL,
  updated_at      DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE roles (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(64) NOT NULL,
  name         VARCHAR(128) NOT NULL,
  description  VARCHAR(255) NOT NULL DEFAULT '',
  created_at   DATETIME NOT NULL,
  updated_at   DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE permissions (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(64) NOT NULL,
  name         VARCHAR(128) NOT NULL,
  description  VARCHAR(255) NOT NULL DEFAULT '',
  created_at   DATETIME NOT NULL,
  updated_at   DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- replaces User.roles[]
CREATE TABLE user_roles (
  user_id  INT UNSIGNED NOT NULL,
  role_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  KEY idx_user_roles_role (role_id),
  CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- replaces Role.permissions[]
CREATE TABLE role_permissions (
  role_id        INT UNSIGNED NOT NULL,
  permission_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_rp_perm (permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Catalog
-- ---------------------------------------------------------------------------
CREATE TABLE works (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title             VARCHAR(255) NOT NULL,
  slug              VARCHAR(255) NULL,
  work_type         VARCHAR(64) NOT NULL DEFAULT 'Text',
  creator           VARCHAR(255) NOT NULL DEFAULT '',
  owner             VARCHAR(255) NOT NULL DEFAULT '',
  copyright_status  VARCHAR(32) NOT NULL DEFAULT 'draft',
  risk_level        VARCHAR(16) NOT NULL DEFAULT 'Low',
  description       TEXT NULL,
  registered_at     DATE NULL,
  created_by        INT UNSIGNED NULL,
  deleted_at        DATETIME NULL,
  created_at        DATETIME NOT NULL,
  updated_at        DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_works_deleted (deleted_at),
  KEY idx_works_type (work_type),
  KEY idx_works_created (created_at),
  CONSTRAINT fk_works_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE owners (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  legal_name   VARCHAR(255) NOT NULL,
  entity_type  VARCHAR(32) NOT NULL DEFAULT 'individual',
  email        VARCHAR(255) NOT NULL DEFAULT '',
  deleted_at   DATETIME NULL,
  created_at   DATETIME NOT NULL,
  updated_at   DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_owners_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- replaces the WorkOwner link collection
CREATE TABLE work_owners (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_id        INT UNSIGNED NOT NULL,
  owner_id       INT UNSIGNED NOT NULL,
  share_percent  DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  deleted_at     DATETIME NULL,
  created_at     DATETIME NOT NULL,
  updated_at     DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_wo_work (work_id),
  KEY idx_wo_owner (owner_id),
  CONSTRAINT fk_wo_work FOREIGN KEY (work_id) REFERENCES works (id) ON DELETE CASCADE,
  CONSTRAINT fk_wo_owner FOREIGN KEY (owner_id) REFERENCES owners (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE licensees (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255) NOT NULL DEFAULT '',
  organization  VARCHAR(255) NOT NULL DEFAULT '',
  notes         TEXT NULL,
  deleted_at    DATETIME NULL,
  created_at    DATETIME NOT NULL,
  updated_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_licensees_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Licensing
-- ---------------------------------------------------------------------------
CREATE TABLE licenses (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_id         INT UNSIGNED NULL,
  licensee_id     INT UNSIGNED NULL,
  license_type    VARCHAR(32) NOT NULL DEFAULT 'non_exclusive',
  license_status  VARCHAR(32) NOT NULL DEFAULT 'draft',
  payment_status  VARCHAR(16) NOT NULL DEFAULT 'unpaid',
  fee_amount      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  territory       VARCHAR(255) NOT NULL DEFAULT '',
  start_date      DATE NULL,
  end_date        DATE NULL,
  deleted_at      DATETIME NULL,
  created_at      DATETIME NOT NULL,
  updated_at      DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_lic_deleted (deleted_at),
  KEY idx_lic_work (work_id),
  KEY idx_lic_licensee (licensee_id),
  KEY idx_lic_status (license_status),
  KEY idx_lic_payment (payment_status),
  KEY idx_lic_enddate (end_date),
  CONSTRAINT fk_lic_work FOREIGN KEY (work_id) REFERENCES works (id) ON DELETE SET NULL,
  CONSTRAINT fk_lic_licensee FOREIGN KEY (licensee_id) REFERENCES licensees (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE usage_reports (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_id          INT UNSIGNED NULL,
  usage_type       VARCHAR(32) NOT NULL DEFAULT 'suspected',
  detected_source  VARCHAR(255) NOT NULL DEFAULT '',
  detected_at      DATETIME NULL,
  notes            TEXT NULL,
  deleted_at       DATETIME NULL,
  created_at       DATETIME NOT NULL,
  updated_at       DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_usage_deleted (deleted_at),
  KEY idx_usage_work (work_id),
  KEY idx_usage_type (usage_type),
  KEY idx_usage_detected (detected_at),
  CONSTRAINT fk_usage_work FOREIGN KEY (work_id) REFERENCES works (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Enforcement
-- ---------------------------------------------------------------------------
CREATE TABLE infringement_cases (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title            VARCHAR(255) NOT NULL,
  work_id          INT UNSIGNED NULL,
  usage_report_id  INT UNSIGNED NULL,
  case_status      VARCHAR(32) NOT NULL DEFAULT 'open',
  priority         VARCHAR(16) NOT NULL DEFAULT 'medium',
  description      TEXT NULL,
  deleted_at       DATETIME NULL,
  created_at       DATETIME NOT NULL,
  updated_at       DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_case_deleted (deleted_at),
  KEY idx_case_work (work_id),
  KEY idx_case_status (case_status),
  CONSTRAINT fk_case_work FOREIGN KEY (work_id) REFERENCES works (id) ON DELETE SET NULL,
  CONSTRAINT fk_case_usage FOREIGN KEY (usage_report_id) REFERENCES usage_reports (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- replaces the embedded Case.notes[] array
CREATE TABLE case_notes (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  case_id     INT UNSIGNED NOT NULL,
  author_id   INT UNSIGNED NULL,
  body        TEXT NOT NULL,
  created_at  DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_note_case (case_id),
  CONSTRAINT fk_note_case FOREIGN KEY (case_id) REFERENCES infringement_cases (id) ON DELETE CASCADE,
  CONSTRAINT fk_note_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Audit & assets
-- ---------------------------------------------------------------------------
CREATE TABLE audit_logs (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  action_type  VARCHAR(64) NOT NULL,
  entity_type  VARCHAR(64) NOT NULL DEFAULT '',
  entity_id    VARCHAR(64) NOT NULL DEFAULT '',
  actor_id     INT UNSIGNED NULL,
  metadata     TEXT NULL,                 -- JSON-encoded (MySQL 5.x has no JSON type)
  created_at   DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_actor (actor_id),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- file bytes live on disk under uploads/; this row is the metadata (was GridFS)
CREATE TABLE work_assets (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_id       INT UNSIGNED NOT NULL,
  filename      VARCHAR(255) NOT NULL,
  stored_name   VARCHAR(255) NOT NULL,
  content_type  VARCHAR(128) NOT NULL DEFAULT 'application/octet-stream',
  size          BIGINT UNSIGNED NOT NULL DEFAULT 0,
  uploaded_by   INT UNSIGNED NULL,
  deleted_at    DATETIME NULL,
  created_at    DATETIME NOT NULL,
  updated_at    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_asset_work (work_id),
  KEY idx_asset_deleted (deleted_at),
  CONSTRAINT fk_asset_work FOREIGN KEY (work_id) REFERENCES works (id) ON DELETE CASCADE,
  CONSTRAINT fk_asset_uploader FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
