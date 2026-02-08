-- Check modules and screens
-- Run this in phpMyAdmin

-- 1. Get all modules
SELECT * FROM ohrm_module;

-- 2. Check the screen we created
SELECT * FROM ohrm_screen WHERE name = 'Password Manager';
