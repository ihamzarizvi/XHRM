-- Fix Mismatches between Doctrine Entities and Database Tables

-- 1. Fix ohrm_vault_item table
ALTER TABLE `ohrm_vault_item` 
  CHANGE `is_favorite` `favorite` TINYINT(1) NOT NULL DEFAULT '0',
  CHANGE `updated_at` `updated_at` DATETIME NULL;

-- 2. Fix ohrm_vault_category table
ALTER TABLE `ohrm_vault_category` 
  CHANGE `updated_at` `updated_at` DATETIME NULL;

-- 3. Add missing type column to ohrm_vault_category if it doesn't exist
-- Entity has: private string $type = 'personal'; (ENUM('personal', 'shared'))
-- SQL was missing this.
ALTER TABLE `ohrm_vault_category` 
  ADD COLUMN IF NOT EXISTS `type` ENUM('personal', 'shared') NOT NULL DEFAULT 'personal' AFTER `color`;

-- 4. Add password_last_changed and password_strength if missing from ohrm_vault_item
ALTER TABLE `ohrm_vault_item`
  ADD COLUMN IF NOT EXISTS `password_strength` INT(11) DEFAULT NULL AFTER `custom_fields_encrypted`,
  ADD COLUMN IF NOT EXISTS `password_last_changed` DATETIME DEFAULT NULL AFTER `password_strength`,
  ADD COLUMN IF NOT EXISTS `last_accessed` DATETIME DEFAULT NULL AFTER `updated_at`;

-- Success message
SELECT 'Database schema synced with entities!' AS status;
