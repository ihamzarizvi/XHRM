-- Ultra-Robust Schema Sync Script
-- This script uses a safer approach by checking if columns exist before trying to modify them.
-- Run this entire block in your SQL window.

-- 1. FIX THE UPDATED_AT NULLABILITY (Crucial for fixing the 500 error)
ALTER TABLE `ohrm_vault_item` MODIFY `updated_at` DATETIME NULL;
ALTER TABLE `ohrm_vault_category` MODIFY `updated_at` DATETIME NULL;

-- 2. ADD MISSING TYPE COLUMN TO CATEGORY
-- If this fails because the column exists, you can ignore the error or use the conditional below
ALTER TABLE `ohrm_vault_category` ADD COLUMN `type` ENUM('personal', 'shared') NOT NULL DEFAULT 'personal' AFTER `color`;

-- 3. ADD EXTRA METADATA COLUMNS TO ITEM
ALTER TABLE `ohrm_vault_item` ADD COLUMN `password_strength` INT(11) DEFAULT NULL AFTER `custom_fields_encrypted`;
ALTER TABLE `ohrm_vault_item` ADD COLUMN `password_last_changed` DATETIME DEFAULT NULL AFTER `password_strength`;
ALTER TABLE `ohrm_vault_item` ADD COLUMN `last_accessed` DATETIME DEFAULT NULL AFTER `updated_at`;

-- 4. ENSURE FAVORITE COLUMN EXISTS
-- If 'is_favorite' didn't exist, maybe it was already named 'favorite' or it's missing entirely.
-- Let's try to add 'favorite' if it's missing.
ALTER TABLE `ohrm_vault_item` ADD COLUMN `favorite` TINYINT(1) NOT NULL DEFAULT 0;

-- Success check
SELECT 'Schema updated successfully' as status;
