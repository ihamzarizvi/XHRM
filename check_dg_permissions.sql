-- Check the data group permissions we just inserted
SELECT urd.id, urd.user_role_id, ur.name as role_name, 
       urd.data_group_id, dg.name as dg_name,
       urd.can_read, urd.can_create, urd.can_update, urd.can_delete, urd.self
FROM ohrm_user_role_data_group urd
LEFT JOIN ohrm_user_role ur ON urd.user_role_id = ur.id
LEFT JOIN ohrm_data_group dg ON urd.data_group_id = dg.id
WHERE urd.id IN (720, 721, 722)
   OR dg.name = 'password_manager';
