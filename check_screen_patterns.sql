-- Check other screens to see their URL patterns
SELECT * FROM ohrm_screen LIMIT 20;

-- Check if there's a module for password manager
SELECT * FROM ohrm_module WHERE name LIKE '%password%';
