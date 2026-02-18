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
        $this->addSql("INSERT INTO ohrm_screen (name, module_id, action_url) 
            SELECT 'View Password Vault', id, '/password-manager' 
            FROM ohrm_module WHERE name = 'password_manager'
            AND NOT EXISTS (SELECT * FROM ohrm_screen WHERE name = 'View Password Vault')");

        // 3. Insert Screen 'Password Manager Admin'
        $this->addSql("INSERT INTO ohrm_screen (name, module_id, action_url) 
            SELECT 'Password Manager Admin', id, '/password-manager/admin' 
            FROM ohrm_module WHERE name = 'password_manager'
            AND NOT EXISTS (SELECT * FROM ohrm_screen WHERE name = 'Password Manager Admin')");

        // 4. Grant Permissions to Admin Role (Full Access) for 'View Password Vault'
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'Admin' AND s.name = 'View Password Vault'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");

        // 5. Grant Permissions to Admin Role for 'Password Manager Admin'
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'Admin' AND s.name = 'Password Manager Admin'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");

        // 6. Grant Permissions to ESS Role (Vault Access Only)
        $this->addSql("INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
            SELECT r.id, s.id, 1, 1, 1, 1
            FROM ohrm_user_role r
            CROSS JOIN ohrm_screen s 
            WHERE r.name = 'ESS' AND s.name = 'View Password Vault'
            AND NOT EXISTS (SELECT * FROM ohrm_user_role_screen urs WHERE urs.user_role_id = r.id AND urs.screen_id = s.id)");

        // 7. Create Data Group for Password Manager API permissions
        $this->addSql("INSERT IGNORE INTO ohrm_data_group (name, description, can_read, can_create, can_update, can_delete)
            VALUES ('password_manager', 'Password Manager Data', 1, 1, 1, 1)");

        // 8. Register all Password Manager API classes in ohrm_api_permission
        $this->addSql("INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
            SELECT 'XHRM\\\\PasswordManager\\\\Api\\\\VaultItemAPI', m.id, dg.id
            FROM ohrm_module m, ohrm_data_group dg
            WHERE m.name = 'password_manager' AND dg.name = 'password_manager'");

        $this->addSql("INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
            SELECT 'XHRM\\\\PasswordManager\\\\Api\\\\VaultCategoryAPI', m.id, dg.id
            FROM ohrm_module m, ohrm_data_group dg
            WHERE m.name = 'password_manager' AND dg.name = 'password_manager'");

        $this->addSql("INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
            SELECT 'XHRM\\\\PasswordManager\\\\Api\\\\VaultShareAPI', m.id, dg.id
            FROM ohrm_module m, ohrm_data_group dg
            WHERE m.name = 'password_manager' AND dg.name = 'password_manager'");

        $this->addSql("INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
            SELECT 'XHRM\\\\PasswordManager\\\\Api\\\\VaultUserKeyAPI', m.id, dg.id
            FROM ohrm_module m, ohrm_data_group dg
            WHERE m.name = 'password_manager' AND dg.name = 'password_manager'");

        $this->addSql("INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
            SELECT 'XHRM\\\\PasswordManager\\\\Api\\\\VaultAdminAPI', m.id, dg.id
            FROM ohrm_module m, ohrm_data_group dg
            WHERE m.name = 'password_manager' AND dg.name = 'password_manager'");

        // 9. Grant data group permissions to Admin role (full access, both self and non-self)
        $this->addSql("INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
            SELECT r.id, dg.id, 1, 1, 1, 1, 0
            FROM ohrm_user_role r, ohrm_data_group dg
            WHERE r.name = 'Admin' AND dg.name = 'password_manager'");

        $this->addSql("INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
            SELECT r.id, dg.id, 1, 1, 1, 1, 1
            FROM ohrm_user_role r, ohrm_data_group dg
            WHERE r.name = 'Admin' AND dg.name = 'password_manager'");

        // 10. Grant data group permissions to ESS role (self access only)
        $this->addSql("INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
            SELECT r.id, dg.id, 1, 1, 1, 1, 1
            FROM ohrm_user_role r, ohrm_data_group dg
            WHERE r.name = 'ESS' AND dg.name = 'password_manager'");
    }

    public function down(Schema $schema): void
    {
        // Remove data group permissions
        $this->addSql("DELETE dgp FROM ohrm_data_group_permission dgp
            INNER JOIN ohrm_data_group dg ON dgp.data_group_id = dg.id
            WHERE dg.name = 'password_manager'");

        // Remove API permissions
        $this->addSql("DELETE ap FROM ohrm_api_permission ap
            INNER JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
            WHERE dg.name = 'password_manager'");

        // Remove data group
        $this->addSql("DELETE FROM ohrm_data_group WHERE name = 'password_manager'");

        // Remove screen permissions
        $this->addSql("DELETE FROM ohrm_user_role_screen WHERE screen_id IN (
            SELECT id FROM ohrm_screen WHERE name IN ('View Password Vault', 'Password Manager Admin')
        )");

        // Remove screens
        $this->addSql("DELETE FROM ohrm_screen WHERE name IN ('View Password Vault', 'Password Manager Admin')");

        // Remove module
        $this->addSql("DELETE FROM ohrm_module WHERE name = 'password_manager'");
    }
}
