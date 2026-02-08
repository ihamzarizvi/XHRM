-- Database Schema Sync for Password Manager
-- Run this in phpMyAdmin to fix the 500 errors

-- 1. Fix ohrm_vault_category table
ALTER TABLE `ohrm_vault_category` 
ADD COLUMN `type` ENUM('personal', 'shared') NOT NULL DEFAULT 'personal' AFTER `name`,
DROP COLUMN `color`;

-- 2. Fix ohrm_vault_item table
ALTER TABLE `ohrm_vault_item` 
CHANGE COLUMN `is_favorite` `favorite` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `password_strength` INT(11) DEFAULT NULL AFTER `custom_fields_encrypted`,
ADD COLUMN `password_last_changed` DATETIME DEFAULT NULL AFTER `password_strength`,
ADD COLUMN `last_accessed` DATETIME DEFAULT NULL AFTER `breach_detected`,
CHANGE COLUMN `item_type` `item_type` ENUM('login', 'card', 'identity', 'note', 'totp') NOT NULL DEFAULT 'login';

-- 3. Verify changes
DESCRIBE `ohrm_vault_category`;
DESCRIBE `ohrm_vault_item`;
