-- Clear old vault items and user keys so everything starts fresh
-- with the new auto-unlock key

-- 1. Delete all old vault items (encrypted with old master password)
DELETE FROM ohrm_vault_item;

-- 2. Delete old user keys (so a fresh salt gets generated on next login)
DELETE FROM ohrm_vault_user_key;

-- 3. Delete old audit logs (optional, clean slate)
DELETE FROM ohrm_vault_audit_log;

-- Verify
SELECT 'vault_items' AS tbl, COUNT(*) AS remaining FROM ohrm_vault_item
UNION ALL
SELECT 'vault_user_keys', COUNT(*) FROM ohrm_vault_user_key
UNION ALL
SELECT 'audit_logs', COUNT(*) FROM ohrm_vault_audit_log;
