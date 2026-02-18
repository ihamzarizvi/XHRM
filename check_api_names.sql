-- See ALL entries in ohrm_api_permission
SELECT id, api_name, module_id, data_group_id FROM ohrm_api_permission ORDER BY id DESC LIMIT 20;

-- See the data group we created
SELECT id, name FROM ohrm_data_group WHERE name = 'password_manager';
