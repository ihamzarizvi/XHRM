-- Add Password Manager menu item to the sidebar
-- Run this SQL on your database

-- First, insert the main "Password Manager" menu item
INSERT INTO `ohrm_menu_item` (`menu_title`, `parent_id`, `level`, `order_hint`, `status`, `screen_id`, `additional_params`)
VALUES ('Password Manager', NULL, 1, 900, 1, NULL, '{"icon": "key", "url": "/password-manager"}');

-- Note: If you get a duplicate entry error, the menu item already exists
-- You can verify by checking: SELECT * FROM ohrm_menu_item WHERE menu_title = 'Password Manager';
