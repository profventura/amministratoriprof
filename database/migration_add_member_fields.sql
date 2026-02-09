-- Migration to add new fields to members table
ALTER TABLE members
ADD COLUMN IF NOT EXISTS studio_name VARCHAR(150) NULL AFTER last_name,
ADD COLUMN IF NOT EXISTS billing_cf_piva VARCHAR(32) NULL AFTER tax_code,
ADD COLUMN IF NOT EXISTS is_revisor TINYINT(1) NOT NULL DEFAULT 0 AFTER billing_cf_piva,
ADD COLUMN IF NOT EXISTS revision_number VARCHAR(50) NULL AFTER is_revisor,
ADD COLUMN IF NOT EXISTS mobile_phone VARCHAR(50) NULL AFTER phone,
ADD COLUMN IF NOT EXISTS province VARCHAR(10) NULL AFTER city,
ADD COLUMN IF NOT EXISTS zip_code VARCHAR(10) NULL AFTER province,
ADD COLUMN IF NOT EXISTS member_number VARCHAR(50) NULL AFTER id,
ADD COLUMN IF NOT EXISTS registration_date DATE NULL AFTER created_at;

-- Add index for member_number if needed
CREATE INDEX IF NOT EXISTS idx_members_member_number ON members(member_number);

-- Update memberships table if needed (currently seems sufficient, but let's check requirements)
-- "Rinnovo data, data pagamento, importo" are usually tracked in payments linked to memberships.
-- However, we can add specific fields to memberships if we want to track them directly there
-- independently of the payments table, or strictly for "Renewal Date".
ALTER TABLE memberships
ADD COLUMN IF NOT EXISTS renewal_date DATE NULL AFTER status;
