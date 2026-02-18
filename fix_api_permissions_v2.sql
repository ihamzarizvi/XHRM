-- 1. Create the data group (if it doesn't exist)
INSERT IGNORE INTO `ohrm_data_group` (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`) 
VALUES ('password_manager', 'Password Manager Data', 1, 1, 1, 1);

-- 2. Get the data group ID
SET @dg_id = (SELECT id FROM `ohrm_data_group` WHERE `name` = 'password_manager' LIMIT 1);

-- 3. Update existing API permissions to link to the data group
UPDATE `ohrm_api_permission` 
SET `data_group_id` = @dg_id 
WHERE `api_name` LIKE 'XHRM\\PasswordManager\\Api\\%';

-- 4. Grant permissions to Admin role (user_role_id = 1), non-self
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @dg_id, 1, 1, 1, 1, 0);

-- 5. Grant permissions to Admin role, self
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (1, @dg_id, 1, 1, 1, 1, 1);

-- 6. Grant permissions to ESS role (user_role_id = 2), self only
INSERT IGNORE INTO `ohrm_data_group_permission` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) 
VALUES (2, @dg_id, 1, 1, 1, 1, 1);

-- Verify
SELECT ap.api_name, ap.data_group_id, dg.name as dg_name
FROM ohrm_api_permission ap
LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE ap.api_name LIKE 'XHRM\\PasswordManager%';
