-- Minimal seed data to quickly test the application
-- Creates one admin user, sample members, memberships, payments, cash categories and flows

USE am_professionisti;

-- Admin user: username=admin, password=password (bcrypt)
INSERT INTO users (username, password_hash, role, active)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F/2Wf7r5K4c5MTNRQH1yK', 'admin', 1);

-- Association settings (receipt sequence for current year)
INSERT INTO settings (association_name, address, city, email, phone, receipt_sequence_current, receipt_sequence_year, updated_at)
VALUES ('Associazione AP', 'Via Roma 1', 'Milano', 'info@associazione-ap.it', '+39 02 123456', 0, YEAR(CURDATE()), NOW());
-- Set default membership certificate template
UPDATE settings SET membership_certificate_template_path=NULL;
UPDATE settings SET membership_certificate_template_docx_path='app/templates/certificato.docx';
UPDATE settings SET dm_certificate_template_docx_path='app/templates/certificato.docx';
UPDATE settings SET certificate_stamp_name_x=100, certificate_stamp_name_y=120, certificate_stamp_number_x=100, certificate_stamp_number_y=140, certificate_stamp_font_size=16;

-- Cash categories
INSERT INTO cash_categories (name, type, active) VALUES
('Quote associative', 'income', 1),
('Donazioni', 'income', 1),
('Spese generiche', 'expense', 1);

-- Members
INSERT INTO members (member_number, first_name, last_name, studio_name, email, phone, mobile_phone, address, city, province, zip_code, birth_date, tax_code, billing_cf_piva, is_revisor, revision_number, status, registration_date, created_at)
VALUES
('001', 'Mario', 'Rossi', 'Studio Rossi', 'mario.rossi@example.com', '+39 02111222', '+39 333111222', 'Via Roma 10', 'Milano', 'MI', '20100', '1985-04-12', 'RSSMRA85D12F205X', 'RSSMRA85D12F205X', 1, 'REV-001', 'active', '2023-01-01', NOW()),
('002', 'Giulia', 'Bianchi', NULL, 'giulia.bianchi@example.com', NULL, '+39 333222333', 'Corso Italia 5', 'Torino', 'TO', '10100', '1990-11-02', 'BNCGLL90S42L219Z', NULL, 0, NULL, 'active', '2023-02-15', NOW()),
('003', 'Luca', 'Verdi', 'Verdi Admin', 'luca.verdi@example.com', '+39 06999888', '+39 333333444', 'Piazza Navona 1', 'Roma', 'RM', '00100', '1982-07-20', 'VRDLCU82L20H501Z', '12345678901', 1, 'REV-002', 'inactive', '2022-05-10', NOW());

-- Memberships for current and previous year
INSERT INTO memberships (member_id, year, status, created_at)
VALUES
((SELECT id FROM members WHERE email='mario.rossi@example.com'), YEAR(CURDATE()), 'pending', NOW()),
((SELECT id FROM members WHERE email='giulia.bianchi@example.com'), YEAR(CURDATE()), 'pending', NOW()),
((SELECT id FROM members WHERE email='luca.verdi@example.com'), YEAR(CURDATE())-1, 'overdue', NOW());

-- Sample payment: Mario pays current year fee
INSERT INTO payments (member_id, membership_id, payment_date, amount, method, notes, receipt_number, receipt_year, created_at)
VALUES (
  (SELECT id FROM members WHERE email='mario.rossi@example.com'),
  (SELECT id FROM memberships WHERE member_id=(SELECT id FROM members WHERE email='mario.rossi@example.com') AND year=YEAR(CURDATE())),
  CURDATE(), 50.00, 'bank', 'Quota annuale', '0001', YEAR(CURDATE()), NOW()
);

-- Update membership status to regular for Mario
UPDATE memberships
SET status='regular'
WHERE member_id=(SELECT id FROM members WHERE email='mario.rossi@example.com') AND year=YEAR(CURDATE());

-- Cash flow for the payment
INSERT INTO cash_flows (flow_date, category_id, description, amount, type, related_payment_id, created_at)
VALUES (
  CURDATE(),
  (SELECT id FROM cash_categories WHERE name='Quote associative' AND type='income'),
  'Quota annuale Mario Rossi',
  50.00,
  'income',
  (SELECT id FROM payments WHERE receipt_number='0001' AND receipt_year=YEAR(CURDATE())),
  NOW()
);

-- Document archive: sample receipt record
INSERT INTO documents (member_id, type, year, file_path, created_at)
VALUES (
  (SELECT id FROM members WHERE email='mario.rossi@example.com'),
  'receipt',
  YEAR(CURDATE()),
  CONCAT('storage/documents/receipts/', YEAR(CURDATE()), '/receipt_0001.html'),
  NOW()
);
