-- Create a proper Module for Password Manager
-- Run this in phpMyAdmin

-- 1. Insert the module
INSERT INTO `ohrm_module` (`name`, `status`) 
VALUES ('passwordManager', 1);

-- 2. Get the new Module ID
SET @module_id = LAST_INSERT_ID();

-- 3. Update our Screen to use this new Module and a clean action name
-- We'll use 'viewPasswordManager' as the action name
UPDATE `ohrm_screen` 
SET `module_id` = @module_id, 
    `action_url` = 'viewPasswordManager' 
WHERE `name` = 'Password Manager';

-- 4. Verify the new setup
SELECT s.id, s.name, m.name as module_name, s.action_url 
FROM ohrm_screen s 
JOIN ohrm_module m ON s.module_id = m.id 
WHERE s.name = 'Password Manager';
