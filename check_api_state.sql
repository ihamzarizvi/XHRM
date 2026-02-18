-- Step 1: See what's currently in ohrm_api_permission for our APIs
SELECT ap.id, ap.api_name, ap.module_id, ap.data_group_id, dg.name as data_group_name
FROM ohrm_api_permission ap
LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE ap.api_name LIKE 'XHRM\\PasswordManager%';

-- Step 2: See what data groups exist
SELECT id, name FROM ohrm_data_group WHERE name LIKE '%password%' OR name LIKE '%vault%';

-- Step 3: See what data group permissions exist for our APIs
SELECT dgp.user_role_id, ur.name as role_name, dgp.data_group_id, dg.name as dg_name,
       dgp.can_read, dgp.can_create, dgp.can_update, dgp.can_delete, dgp.self
FROM ohrm_data_group_permission dgp
LEFT JOIN ohrm_user_role ur ON dgp.user_role_id = ur.id
LEFT JOIN ohrm_data_group dg ON dgp.data_group_id = dg.id
WHERE dg.name LIKE '%password%' OR dg.name IS NULL
LIMIT 20;
