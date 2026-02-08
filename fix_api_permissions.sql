-- Fix API permissions for Password Manager
-- Run this in phpMyAdmin

-- 1. Create a Data Group for Password Manager
INSERT IGNORE INTO `ohrm_data_group` (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`) 
VALUES ('password_manager', 'Password Manager Data', 1, 1, 1, 1);

-- 2. Get the Data Group ID
SET @data_group_id = (SELECT id FROM `ohrm_data_group` WHERE `name` = 'password_manager' LIMIT 1);

-- 3. Link APIs to the Data Group
-- For Vault Items
INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`) 
VALUES ('XHRM\\PasswordManager\\Api\\VaultItemAPI', 100, @data_group_id);

-- For Vault Categories
INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`) 
VALUES ('XHRM\\PasswordManager\\Api\\VaultCategoryAPI', 100, @data_group_id);

-- 4. Grant permissions to Admin role (ID 1)
-- Non-self permissions
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @data_group_id, 1, 1, 1, 1, 0);

-- Self permissions (so users can see their own passwords)
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @data_group_id, 1, 1, 1, 1, 1);

-- Also try for Role ID 2 just in case
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (2, @data_group_id, 1, 1, 1, 1, 0);

INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (2, @data_group_id, 1, 1, 1, 1, 1);

-- Success message
SELECT 'API Permissions granted successfully!' AS status;
