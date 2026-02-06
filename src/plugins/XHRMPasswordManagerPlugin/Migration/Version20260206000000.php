<?php

namespace XHRM\PasswordManager\Migration;

use Doctrine\DBAL\Schema\Schema;
use XHRM\Core\Migration\AbstractMigration;

class Version20260206000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE ohrm_vault_category (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50),
            user_id INT NOT NULL,
            type ENUM('personal', 'shared') DEFAULT 'personal',
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES ohrm_user(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        )");

        $this->addSql("CREATE TABLE ohrm_vault_item (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT,
            item_type ENUM('login', 'card', 'identity', 'note', 'totp') NOT NULL,
            name VARCHAR(255) NOT NULL,
            favorite BOOLEAN DEFAULT FALSE,
            username_encrypted TEXT,
            password_encrypted TEXT,
            url_encrypted TEXT,
            notes_encrypted TEXT,
            totp_secret_encrypted TEXT,
            custom_fields_encrypted TEXT,
            password_strength INT,
            password_last_changed DATETIME,
            breach_detected BOOLEAN DEFAULT FALSE,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            last_accessed DATETIME,
            FOREIGN KEY (user_id) REFERENCES ohrm_user(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES ohrm_vault_category(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_category_id (category_id),
            INDEX idx_item_type (item_type)
        )");

        $this->addSql("CREATE TABLE ohrm_vault_share (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vault_item_id INT NOT NULL,
            shared_by_user_id INT NOT NULL,
            shared_with_user_id INT NOT NULL,
            permission ENUM('read', 'write', 'admin') DEFAULT 'read',
            encrypted_key_for_recipient TEXT,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (vault_item_id) REFERENCES ohrm_vault_item(id) ON DELETE CASCADE,
            FOREIGN KEY (shared_by_user_id) REFERENCES ohrm_user(id) ON DELETE CASCADE,
            FOREIGN KEY (shared_with_user_id) REFERENCES ohrm_user(id) ON DELETE CASCADE,
            INDEX idx_shared_with (shared_with_user_id),
            INDEX idx_item_id (vault_item_id)
        )");

        $this->addSql("CREATE TABLE ohrm_vault_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vault_item_id INT,
            action ENUM('created', 'viewed', 'updated', 'deleted', 'password_copied', 'shared', 'unshared') NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES ohrm_user(id) ON DELETE CASCADE,
            FOREIGN KEY (vault_item_id) REFERENCES ohrm_vault_item(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        )");

        // Add configuration settings
        $this->addSql("INSERT INTO ohrm_config (key, value) VALUES
            ('password_manager.enabled', '0'),
            ('password_manager.require_master_password', '1'),
            ('password_manager.session_timeout', '15'),
            ('password_manager.max_vault_items', '1000')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE ohrm_vault_audit_log");
        $this->addSql("DROP TABLE ohrm_vault_share");
        $this->addSql("DROP TABLE ohrm_vault_item");
        $this->addSql("DROP TABLE ohrm_vault_category");
        $this->addSql("DELETE FROM ohrm_config WHERE key LIKE 'password_manager.%'");
    }
}
