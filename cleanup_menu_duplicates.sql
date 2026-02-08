-- Clean up duplicate Password Manager menu items and keep only one

-- Delete ALL Password Manager entries
DELETE FROM ohrm_menu_item WHERE menu_title = 'Password Manager';

-- Insert ONE clean entry with simpler additional_params
INSERT INTO ohrm_menu_item (menu_title, parent_id, level, order_hint, status, screen_id, additional_params)
VALUES ('Password Manager', NULL, 1, 900, 1, NULL, '{"url": "/password-manager"}');

-- Verify the result
SELECT id, menu_title, parent_id, level, order_hint, status, additional_params 
FROM ohrm_menu_item 
WHERE menu_title = 'Password Manager';
