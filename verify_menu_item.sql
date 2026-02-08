-- Check and fix Password Manager menu item
-- Run this SQL in phpMyAdmin

-- First, check if the menu item exists
SELECT * FROM ohrm_menu_item WHERE menu_title = 'Password Manager';

-- If it doesn't exist or needs to be recreated, delete and insert:
DELETE FROM ohrm_menu_item WHERE menu_title = 'Password Manager';

-- Insert with correct structure (matching other menu items)
INSERT INTO ohrm_menu_item (menu_title, parent_id, level, order_hint, status, screen_id, additional_params)
VALUES ('Password Manager', NULL, 1, 900, 1, NULL, NULL);

-- Verify insertion
SELECT * FROM ohrm_menu_item WHERE menu_title = 'Password Manager';

-- Also check what other menu items look like for reference
SELECT id, menu_title, parent_id, level, order_hint, status, screen_id, additional_params 
FROM ohrm_menu_item 
WHERE level = 1 
ORDER BY order_hint;
