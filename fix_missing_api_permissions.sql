SET @data_group_id = (SELECT id FROM `ohrm_data_group` WHERE `name` = 'password_manager' LIMIT 1);

INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`) 
VALUES ('XHRM\\PasswordManager\\Api\\VaultShareAPI', 100, @data_group_id);

INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`) 
VALUES ('XHRM\\PasswordManager\\Api\\VaultUserKeyAPI', 100, @data_group_id);

INSERT IGNORE INTO `ohrm_api_permission` (`api_name`, `module_id`, `data_group_id`) 
VALUES ('XHRM\\PasswordManager\\Api\\VaultAdminAPI', 100, @data_group_id);

SELECT CONCAT('data_group_id = ', IFNULL(@data_group_id, 'NULL - run fix_api_permissions.sql first!')) AS status;
