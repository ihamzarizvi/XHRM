-- Fix: directly link the new APIs to the password_manager data group
SET @dg_id = (SELECT id FROM `ohrm_data_group` WHERE `name` = 'password_manager' LIMIT 1);

UPDATE `ohrm_api_permission` 
SET `data_group_id` = @dg_id 
WHERE `api_name` IN (
    'XHRM\\PasswordManager\\Api\\VaultUserKeyAPI',
    'XHRM\\PasswordManager\\Api\\VaultAuditLogAPI',
    'XHRM\\PasswordManager\\Api\\VaultShareAPI',
    'XHRM\\PasswordManager\\Api\\VaultItemAPI',
    'XHRM\\PasswordManager\\Api\\VaultCategoryAPI',
    'XHRM\\PasswordManager\\Api\\VaultAdminAPI'
);

-- Verify
SELECT ap.api_name, ap.data_group_id, dg.name AS dg_name
FROM ohrm_api_permission ap
LEFT JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE ap.api_name LIKE '%PasswordManager%'
ORDER BY ap.api_name;
