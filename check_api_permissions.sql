-- Check if VaultItemAPI is registered in permissions
SELECT ap.*, dg.name as data_group_name 
FROM ohrm_api_permission ap
JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE ap.api_name LIKE '%VaultItemAPI%';

-- Check if any roles have access to this data group
SELECT dgp.*, r.name as role_name
FROM ohrm_data_group_permission dgp
JOIN ohrm_user_role r ON dgp.user_role_id = r.id
JOIN ohrm_data_group dg ON dgp.data_group_id = dg.id
WHERE dg.name IN (
    SELECT dg2.name 
    FROM ohrm_api_permission ap2
    JOIN ohrm_data_group dg2 ON ap2.data_group_id = dg2.id
    WHERE ap2.api_name LIKE '%VaultItemAPI%'
);
