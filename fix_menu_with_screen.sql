-- Fix Password Manager menu by creating a screen and linking it properly

-- Step 1: Create a screen for Password Manager
INSERT INTO ohrm_screen (name, module_id, action_url)
VALUES ('Password Manager', 1, '/password-manager');

-- Step 2: Get the screen ID we just created
SET @screen_id = LAST_INSERT_ID();

-- Step 3: Delete the old menu item
DELETE FROM ohrm_menu_item WHERE menu_title = 'Password Manager';

-- Step 4: Insert menu item with proper screen_id
INSERT INTO ohrm_menu_item (menu_title, parent_id, level, order_hint, status, screen_id, additional_params)
VALUES ('Password Manager', NULL, 1, 900, 1, @screen_id, NULL);

-- Step 5: Verify the result
SELECT m.id, m.menu_title, m.screen_id, s.name as screen_name, s.action_url
FROM ohrm_menu_item m
LEFT JOIN ohrm_screen s ON m.screen_id = s.id
WHERE m.menu_title = 'Password Manager';
