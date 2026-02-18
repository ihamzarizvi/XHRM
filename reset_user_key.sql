-- Delete the corrupted user key so a fresh one gets generated on next login
DELETE FROM ohrm_vault_user_key WHERE user_id = 1;

-- Verify it's gone
SELECT COUNT(*) as remaining_keys FROM ohrm_vault_user_key;
