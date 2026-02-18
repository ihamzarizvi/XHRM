-- Reset vault user keys so they get regenerated with the correct JSON format
-- (salt + rsaPublicKey stored together)
-- Run this ONCE after deploying the fix.

-- Delete existing user keys (they have broken publicKey format)
DELETE FROM ohrm_vault_user_key;

-- Also clear vault items since they were encrypted with the wrong key
DELETE FROM ohrm_vault_item;
DELETE FROM ohrm_vault_audit_log;

-- Verify
SELECT 'vault_user_keys' AS tbl, COUNT(*) AS remaining FROM ohrm_vault_user_key
UNION ALL
SELECT 'vault_items', COUNT(*) FROM ohrm_vault_item;
