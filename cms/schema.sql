CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE COLLATE NOCASE,
  display_name TEXT NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ('super_admin','admin')),
  status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','disabled')),
  must_change_password INTEGER NOT NULL DEFAULT 1,
  failed_attempts INTEGER NOT NULL DEFAULT 0,
  locked_until TEXT,
  last_login_at TEXT,
  created_by INTEGER,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY(created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS materials (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title_zh TEXT NOT NULL,
  title_ru TEXT NOT NULL DEFAULT '',
  title_en TEXT NOT NULL DEFAULT '',
  description_zh TEXT NOT NULL DEFAULT '',
  description_ru TEXT NOT NULL DEFAULT '',
  description_en TEXT NOT NULL DEFAULT '',
  category TEXT NOT NULL DEFAULT 'document',
  original_name TEXT NOT NULL,
  storage_name TEXT NOT NULL UNIQUE,
  mime_type TEXT NOT NULL,
  file_size INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft','published')),
  sort_order INTEGER NOT NULL DEFAULT 100,
  created_by INTEGER NOT NULL,
  updated_by INTEGER NOT NULL,
  deleted_at TEXT,
  deleted_by INTEGER,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY(created_by) REFERENCES users(id),
  FOREIGN KEY(updated_by) REFERENCES users(id),
  FOREIGN KEY(deleted_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS members (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT NOT NULL UNIQUE COLLATE NOCASE,
  member_type TEXT NOT NULL DEFAULT 'person' CHECK(member_type IN ('person','organization')),
  section TEXT NOT NULL DEFAULT 'member' CHECK(section IN ('council','secretariat','member')),
  name_zh TEXT NOT NULL,
  name_ru TEXT NOT NULL DEFAULT '',
  name_en TEXT NOT NULL DEFAULT '',
  role_zh TEXT NOT NULL DEFAULT '',
  role_ru TEXT NOT NULL DEFAULT '',
  role_en TEXT NOT NULL DEFAULT '',
  organization_zh TEXT NOT NULL DEFAULT '',
  organization_ru TEXT NOT NULL DEFAULT '',
  organization_en TEXT NOT NULL DEFAULT '',
  summary_zh TEXT NOT NULL DEFAULT '',
  summary_ru TEXT NOT NULL DEFAULT '',
  summary_en TEXT NOT NULL DEFAULT '',
  bio_zh TEXT NOT NULL DEFAULT '',
  bio_ru TEXT NOT NULL DEFAULT '',
  bio_en TEXT NOT NULL DEFAULT '',
  photo_path TEXT NOT NULL DEFAULT '',
  status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft','published')),
  is_demo INTEGER NOT NULL DEFAULT 0,
  sort_order INTEGER NOT NULL DEFAULT 100,
  created_by INTEGER,
  updated_by INTEGER,
  deleted_at TEXT,
  deleted_by INTEGER,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY(created_by) REFERENCES users(id),
  FOREIGN KEY(updated_by) REFERENCES users(id),
  FOREIGN KEY(deleted_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  action TEXT NOT NULL,
  entity_type TEXT NOT NULL,
  entity_id INTEGER,
  details_json TEXT NOT NULL DEFAULT '{}',
  ip_hash TEXT NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_materials_public ON materials(status, deleted_at, sort_order);
CREATE INDEX IF NOT EXISTS idx_members_public ON members(status, deleted_at, section, sort_order);
CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at DESC);
