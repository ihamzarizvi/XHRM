-- Register new Password Manager API endpoints and fix permissions
-- Run this in phpMyAdmin on the server database

-- 1. Register the new API endpoints in ohrm_api_permission (if not already there)
INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`)
SELECT 
    api_name,
    (SELECT id FROM ohrm_module WHERE name = 'xhrmPasswordManager' LIMIT 1) AS module_id,
    (SELECT id FROM ohrm_data_group WHERE name = 'password_manager' LIMIT 1) AS data_group_id
FROM (
    SELECT 'XHRM\\PasswordManager\\Api\\VaultUserKeyAPI' AS api_name
    UNION SELECT 'XHRM\\PasswordManager\\Api\\VaultAuditLogAPI'
    UNION SELECT 'XHRM\\PasswordManager\\Api\\VaultShareAPI'
) AS new_apis;

-- 2. Make sure data group exists
INSERT IGNORE INTO `ohrm_data_group` (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`) 
VALUES ('password_manager', 'Password Manager Data', 1, 1, 1, 1);

-- 3. Re-link ALL password manager APIs to the data group
SET @dg_id = (SELECT id FROM `ohrm_data_group` WHERE `name` = 'password_manager' LIMIT 1);

UPDATE `ohrm_api_permission` 
SET `data_group_id` = @dg_id 
WHERE `api_name` LIKE 'XHRM\\\\PasswordManager\\\\Api\\\\%';

-- 4. Grant Admin role full access (non-self)
INSERT IGNORE INTO `ohrm_user_role_data_group` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @dg_id, 1, 1, 1, 1, 0);

-- 5. Grant Admin role full access (self)
INSERT IGNORE INTO `ohrm_user_role_data_group` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @dg_id, 1, 1, 1, 1, 1);

-- 6. Grant ESS role self-only access
INSERT IGNORE INTO `ohrm_user_role_data_group` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (2, @dg_id, 1, 1, 1, 1, 1);

-- Verify: check all registered PM APIs and their data group
SELECT ap.api_name, ap.data_group_id, dg.name AS dg_name
FROM ohrm_api_permission ap
LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE ap.api_name LIKE 'XHRM\\PasswordManager%'
ORDER BY ap.api_name;
