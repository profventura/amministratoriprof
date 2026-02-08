-- Database schema for am_professionisti
-- Creates all tables, keys, indexes with correct types

-- CREATE DATABASE IF NOT EXISTS am_professionisti
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_0900_ai_ci;

-- USE am_professionisti;

-- Users (single administrator)
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin') NOT NULL DEFAULT 'admin',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Members (soci)
CREATE TABLE IF NOT EXISTS members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(50) NULL,
  address VARCHAR(190) NULL,
  city VARCHAR(120) NULL,
  birth_date DATE NULL,
  tax_code VARCHAR(32) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_members_email (email)
) ENGINE=InnoDB;

-- Memberships (iscrizioni annuali)
CREATE TABLE IF NOT EXISTS memberships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id INT UNSIGNED NOT NULL,
  year INT NOT NULL,
  status ENUM('pending','regular','overdue') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_memberships_member_year (member_id, year),
  CONSTRAINT fk_memberships_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Payments (pagamenti)
CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id INT UNSIGNED NOT NULL,
  membership_id INT UNSIGNED NULL,
  payment_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('cash','bank','card') NOT NULL,
  notes VARCHAR(255) NULL,
  receipt_number VARCHAR(50) NULL,
  receipt_year INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_payments_member (member_id),
  KEY idx_payments_membership (membership_id),
  UNIQUE KEY uq_receipt_year_number (receipt_year, receipt_number),
  CONSTRAINT fk_payments_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_payments_membership
    FOREIGN KEY (membership_id) REFERENCES memberships(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Documents (documenti PDF/HTML archiviati)
CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id INT UNSIGNED NULL,
  type ENUM('membership_certificate','receipt','dm_certificate') NOT NULL,
  year INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_documents_member (member_id),
  KEY idx_documents_type_year (type, year),
  CONSTRAINT fk_documents_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Cash categories (categorie di cassa)
CREATE TABLE IF NOT EXISTS cash_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_cash_categories_name_type (name, type)
) ENGINE=InnoDB;

-- Cash flows (movimenti di cassa)
CREATE TABLE IF NOT EXISTS cash_flows (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  flow_date DATE NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description VARCHAR(190) NULL,
  amount DECIMAL(10,2) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  related_payment_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cash_flows_category (category_id),
  KEY idx_cash_flows_type_date (type, flow_date),
  CONSTRAINT fk_cash_flows_category
    FOREIGN KEY (category_id) REFERENCES cash_categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_cash_flows_payment
    FOREIGN KEY (related_payment_id) REFERENCES payments(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Courses (corsi)
CREATE TABLE IF NOT EXISTS courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  course_date DATE NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  year INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Course participants (partecipanti ai corsi)
CREATE TABLE IF NOT EXISTS course_participants (
  course_id INT UNSIGNED NOT NULL,
  member_id INT UNSIGNED NOT NULL,
  certificate_document_id INT UNSIGNED NULL,
  PRIMARY KEY (course_id, member_id),
  KEY idx_course_participants_member (member_id),
  CONSTRAINT fk_course_participants_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_course_participants_member
    FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_course_participants_certificate
    FOREIGN KEY (certificate_document_id) REFERENCES documents(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Email settings (SMTP configurabile)
CREATE TABLE IF NOT EXISTS email_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  smtp_host VARCHAR(190) NOT NULL,
  smtp_port INT NOT NULL,
  smtp_secure ENUM('none','tls','ssl') NOT NULL DEFAULT 'none',
  username VARCHAR(190) NULL,
  password VARCHAR(190) NULL,
  from_email VARCHAR(190) NOT NULL,
  from_name VARCHAR(190) NOT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

-- Email logs (log invii)
CREATE TABLE IF NOT EXISTS email_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  to_email VARCHAR(190) NOT NULL,
  cc VARCHAR(255) NULL,
  bcc VARCHAR(255) NULL,
  subject VARCHAR(190) NOT NULL,
  body TEXT NOT NULL,
  status ENUM('sent','failed') NOT NULL,
  error_message VARCHAR(255) NULL
) ENGINE=InnoDB;

-- Settings (impostazioni associazione e numerazioni)
CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  association_name VARCHAR(190) NOT NULL,
  address VARCHAR(190) NULL,
  city VARCHAR(120) NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(50) NULL,
  receipt_sequence_current INT NOT NULL DEFAULT 0,
  receipt_sequence_year INT NOT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

-- Migration: add membership certificate template path
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS membership_certificate_template_path VARCHAR(255) NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS dm_certificate_template_docx_path VARCHAR(255) NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS membership_certificate_template_docx_path VARCHAR(255) NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS certificate_stamp_name_x INT NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS certificate_stamp_name_y INT NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS certificate_stamp_number_x INT NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS certificate_stamp_number_y INT NULL;
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS certificate_stamp_font_size INT NULL;

