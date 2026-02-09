-- Direct SQL script to register Password Manager module
-- Run this in phpMyAdmin or your Hostinger database manager

-- 1. Insert Module (if not exists)
INSERT INTO `ohrm_module` (`name`, `status`)
SELECT 'passwordManager', 1
WHERE NOT EXISTS (SELECT 1 FROM `ohrm_module` WHERE `name` = 'passwordManager');

-- 2. Insert Screen (if not exists)
INSERT INTO `ohrm_screen` (`name`, `module_id`, `action_url`)
SELECT 'Password Manager', 
       (SELECT `id` FROM `ohrm_module` WHERE `name` = 'passwordManager'), 
       'viewPasswordManager'
WHERE NOT EXISTS (SELECT 1 FROM `ohrm_screen` WHERE `name` = 'Password Manager');

-- 3. Insert Menu Item (if not exists)
INSERT INTO `ohrm_menu_item` (`menu_title`, `screen_id`, `parent_id`, `level`, `order_hint`, `additional_params`, `status`)
SELECT 'Password Manager', 
       (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager'), 
       NULL, 1, 1100, NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM `ohrm_menu_item` WHERE `menu_title` = 'Password Manager');

-- 4. Grant Admin Permissions (if not exists)
INSERT INTO `ohrm_user_role_screen` (`user_role_id`, `screen_id`, `can_read`, `can_create`, `can_update`, `can_delete`)
SELECT 1, 
       (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager'), 
       1, 1, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM `ohrm_user_role_screen` 
    WHERE `user_role_id` = 1 
    AND `screen_id` = (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager')
);

-- 5. Verify the registration
SELECT 
    m.id as module_id,
    m.name AS module_name, 
    s.id as screen_id,
    s.name AS screen_name, 
    s.action_url,
    mi.id as menu_id,
    mi.menu_title,
    mi.order_hint,
    urs.can_read,
    urs.can_create,
    urs.can_update,
    urs.can_delete
FROM ohrm_module m
JOIN ohrm_screen s ON s.module_id = m.id
LEFT JOIN ohrm_menu_item mi ON mi.screen_id = s.id
LEFT JOIN ohrm_user_role_screen urs ON urs.screen_id = s.id AND urs.user_role_id = 1
WHERE m.name = 'passwordManager';
