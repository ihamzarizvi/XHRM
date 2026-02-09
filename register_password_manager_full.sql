-- Comprehensive Password Manager Registration Script

-- 1. Insert Module (if not exists)
INSERT INTO `ohrm_module` (`name`, `status`)
SELECT 'passwordManager', 1
WHERE NOT EXISTS (SELECT * FROM `ohrm_module` WHERE `name` = 'passwordManager');

-- 2. Get Module ID
SET @moduleId = (SELECT `id` FROM `ohrm_module` WHERE `name` = 'passwordManager');

-- 3. Insert Screen (if not exists)
INSERT INTO `ohrm_screen` (`name`, `module_id`, `action_url`)
SELECT 'Password Manager', @moduleId, 'viewPasswordManager'
WHERE NOT EXISTS (SELECT * FROM `ohrm_screen` WHERE `name` = 'Password Manager');

-- 4. Get Screen ID
SET @screenId = (SELECT `id` FROM `ohrm_screen` WHERE `name` = 'Password Manager');

-- 5. Insert Menu Item (Main Level)
INSERT INTO `ohrm_menu_item` (`menu_title`, `screen_id`, `parent_id`, `level`, `order_hint`, `url_extras`, `status`)
SELECT 'Password Manager', @screenId, NULL, 1, 1100, NULL, 1
WHERE NOT EXISTS (SELECT * FROM `ohrm_menu_item` WHERE `menu_title` = 'Password Manager');

-- 6. Grant Permissions to Admin (Role ID 1)
INSERT INTO `ohrm_user_role_screen` (`user_role_id`, `screen_id`, `can_read`, `can_create`, `can_update`, `can_delete`)
SELECT 1, @screenId, 1, 1, 1, 1
WHERE NOT EXISTS (SELECT * FROM `ohrm_user_role_screen` WHERE `user_role_id` = 1 AND `screen_id` = @screenId);

SELECT 
    m.name AS Module, 
    s.name AS Screen, 
    mi.menu_title AS Menu, 
    urs.can_read AS Admin_Access 
FROM ohrm_module m
JOIN ohrm_screen s ON s.module_id = m.id
JOIN ohrm_menu_item mi ON mi.screen_id = s.id
JOIN ohrm_user_role_screen urs ON urs.screen_id = s.id
WHERE m.name = 'passwordManager';
