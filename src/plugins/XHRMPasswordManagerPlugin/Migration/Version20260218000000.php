<?php

namespace XHRM\PasswordManager\Migration;

use Doctrine\DBAL\Schema\Schema;
use XHRM\Core\Migration\AbstractMigration;

class Version20260218000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // 1. Insert Module 'password_manager'
        $this->addSql("INSERT INTO ohrm_module (name, display_name, status) 
            SELECT 'password_manager', 'Password Manager', 1 
            FROM DUAL 
            WHERE NOT EXISTS (SELECT * FROM ohrm_module WHERE name = 'password_manager')");

        // 2. Insert Screen 'View Password Vault'
        // Using subquery for module_id
        $this->addSql("INSERT INTO ohrm_screen (name, module_id, action_url) 
            SELECT 'View Password Vault', id, '/password-manager' 
            FROM ohrm_module WHERE name = 'password_manager'");

        // 3. Insert Screen 'Password Manager Admin'
        $this->addSql("INSERT INTO ohrm_screen (name, module_id, action_url) 
            SELECT 'Password Manager Admin', id, '/password-manager/admin' 
            FROM ohrm_module WHERE name = 'password_manager'");

        // 4. Grant Permissions to Admin Role (Full Access)
        // Assuming role name 'Admin' exists.

        // For 'View Password Vault'
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'Admin' AND s.name = 'View Password Vault'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");

        // For 'Password Manager Admin'
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'Admin' AND s.name = 'Password Manager Admin'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");

        // 5. Grant Permissions to ESS Role (Vault Access Only)
        // Assuming role name 'ESS' exists.
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'ESS' AND s.name = 'View Password Vault'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");
    }

    public function down(Schema $schema): void
    {
        // 1. Delete permissions
        $this->addSql("DELETE FROM ohrm_user_role_screen WHERE screen_id IN (
            SELECT id FROM ohrm_screen WHERE name IN ('View Password Vault', 'Password Manager Admin')
        )");

        // 2. Delete screens
        $this->addSql("DELETE FROM ohrm_screen WHERE name IN ('View Password Vault', 'Password Manager Admin')");

        // 3. Delete module
        $this->addSql("DELETE FROM ohrm_module WHERE name = 'password_manager'");
    }
}
