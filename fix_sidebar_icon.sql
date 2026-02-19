-- Fix: Add icon to Password Manager sidebar menu item
-- The icon is stored in the additional_params JSON column of ohrm_menu_item
-- Run this on the production database to add the shield-lock icon

UPDATE ohrm_menu_item
SET additional_params = JSON_SET(
    COALESCE(additional_params, '{}'),
    '$.icon', 'bi-shield-lock'
)
WHERE menu_title = 'Password Manager';
