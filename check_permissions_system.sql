-- Check User Roles and Permissions tables
-- Run this in phpMyAdmin to see available roles and how permissions are mapped

-- 1. Get all user roles
SELECT * FROM ohrm_user_role;

-- 2. See how permissions are mapped to screens (usually ohrm_user_role_screen)
-- This will help us understand the table structure
SHOW CREATE TABLE ohrm_user_role_screen;

-- 3. Check current permissions for the first few screens to see the pattern
SELECT * FROM ohrm_user_role_screen LIMIT 10;
