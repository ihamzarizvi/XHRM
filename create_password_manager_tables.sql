-- Password Manager Database Tables
-- Run this SQL to create all required tables

-- 1. Vault Categories Table
CREATE TABLE IF NOT EXISTS `ohrm_vault_category` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Vault Items Table
CREATE TABLE IF NOT EXISTS `ohrm_vault_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `item_type` VARCHAR(50) NOT NULL DEFAULT 'login',
  `name` VARCHAR(255) NOT NULL,
  `username_encrypted` TEXT DEFAULT NULL,
  `password_encrypted` TEXT DEFAULT NULL,
  `url_encrypted` TEXT DEFAULT NULL,
  `notes_encrypted` TEXT DEFAULT NULL,
  `totp_secret_encrypted` TEXT DEFAULT NULL,
  `custom_fields_encrypted` TEXT DEFAULT NULL,
  `is_favorite` TINYINT(1) NOT NULL DEFAULT 0,
  `breach_detected` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_item_type` (`item_type`),
  CONSTRAINT `fk_vault_item_category` FOREIGN KEY (`category_id`) REFERENCES `ohrm_vault_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Vault Share Table
CREATE TABLE IF NOT EXISTS `ohrm_vault_share` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `vault_item_id` INT(11) NOT NULL,
  `shared_with_user_id` INT(11) NOT NULL,
  `permission` VARCHAR(20) NOT NULL DEFAULT 'read',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vault_item_id` (`vault_item_id`),
  KEY `idx_shared_with_user_id` (`shared_with_user_id`),
  CONSTRAINT `fk_vault_share_item` FOREIGN KEY (`vault_item_id`) REFERENCES `ohrm_vault_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Vault Audit Log Table
CREATE TABLE IF NOT EXISTS `ohrm_vault_audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `vault_item_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vault_item_id` (`vault_item_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_vault_audit_item` FOREIGN KEY (`vault_item_id`) REFERENCES `ohrm_vault_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Success message
SELECT 'Password Manager tables created successfully!' AS status;
