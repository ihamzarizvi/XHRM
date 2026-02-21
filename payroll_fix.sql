-- =====================================================
-- PAYROLL API PERMISSIONS FIX
-- This registers all payroll APIs with the data group 
-- permission system so they stop returning "Unauthorized"
-- =====================================================

-- Step 1: Create data group for Payroll
INSERT IGNORE INTO ohrm_data_group (name, description, can_read, can_create, can_update, can_delete)
VALUES ('payroll', 'Payroll Module Data', 1, 1, 1, 1);

-- Step 2: Register all Payroll API classes in ohrm_api_permission
-- Note: The api_name must match the FQCN with double backslashes as stored

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\SalaryComponentAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\AttendanceRuleAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\OvertimeRuleAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\HolidayAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\FinancialYearAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\TaxSlabAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\PayrollRunAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\PayrollApprovalAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\PayslipAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\MyPayslipAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\EmployeeLoanAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

INSERT IGNORE INTO ohrm_api_permission (api_name, module_id, data_group_id)
SELECT 'XHRM\\Payroll\\Api\\PayrollEmailAPI', m.id, dg.id
FROM ohrm_module m, ohrm_data_group dg WHERE m.name = 'payroll' AND dg.name = 'payroll';

-- Step 3: Grant data group permissions to Admin role (full access, non-self)
INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
SELECT r.id, dg.id, 1, 1, 1, 1, 0
FROM ohrm_user_role r, ohrm_data_group dg
WHERE r.name = 'Admin' AND dg.name = 'payroll';

-- Step 4: Grant data group permissions to Admin role (self)
INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
SELECT r.id, dg.id, 1, 1, 1, 1, 1
FROM ohrm_user_role r, ohrm_data_group dg
WHERE r.name = 'Admin' AND dg.name = 'payroll';

-- Step 5: Grant ESS role read access for My Payslips
INSERT IGNORE INTO ohrm_data_group_permission (user_role_id, data_group_id, can_read, can_create, can_update, can_delete, self)
SELECT r.id, dg.id, 1, 0, 0, 0, 1
FROM ohrm_user_role r, ohrm_data_group dg
WHERE r.name = 'ESS' AND dg.name = 'payroll';

-- Verify
SELECT '=== DATA GROUP ===' AS section;
SELECT * FROM ohrm_data_group WHERE name = 'payroll';

SELECT '=== API PERMISSIONS ===' AS section;
SELECT ap.id, ap.api_name, m.name AS module_name, dg.name AS data_group
FROM ohrm_api_permission ap
JOIN ohrm_module m ON ap.module_id = m.id
JOIN ohrm_data_group dg ON ap.data_group_id = dg.id
WHERE dg.name = 'payroll';

SELECT '=== DATA GROUP PERMISSIONS ===' AS section;
SELECT ur.name AS role_name, dg.name AS data_group, p.can_read, p.can_create, p.can_update, p.can_delete, p.self
FROM ohrm_data_group_permission p
JOIN ohrm_user_role ur ON p.user_role_id = ur.id
JOIN ohrm_data_group dg ON p.data_group_id = dg.id
WHERE dg.name = 'payroll';
