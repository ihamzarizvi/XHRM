-- =====================================================
-- FIX: action_url should be just the screen name, NOT the full path
-- Framework builds URL as: {baseUrl}/{module_name}/{action_url}
-- So action_url should be "generatePayroll" not "/payroll/generatePayroll"
-- =====================================================

UPDATE ohrm_screen SET action_url = 'generatePayroll' WHERE module_id = 102 AND name = 'Generate Payroll';
UPDATE ohrm_screen SET action_url = 'payrollRuns' WHERE module_id = 102 AND name = 'Payroll Runs';
UPDATE ohrm_screen SET action_url = 'approvePayroll' WHERE module_id = 102 AND name = 'Approve Payroll';
UPDATE ohrm_screen SET action_url = 'approvePayroll/viewApprovePayrollDetail' WHERE module_id = 102 AND name = 'Approve Payroll Detail';
UPDATE ohrm_screen SET action_url = 'employeePayslips' WHERE module_id = 102 AND name = 'Employee Payslips';
UPDATE ohrm_screen SET action_url = 'payslip/viewPayslipDetail' WHERE module_id = 102 AND name = 'Payslip Detail';
UPDATE ohrm_screen SET action_url = 'myPayslips' WHERE module_id = 102 AND name = 'My Payslips';
UPDATE ohrm_screen SET action_url = 'loans' WHERE module_id = 102 AND name = 'Loans';
UPDATE ohrm_screen SET action_url = 'holidayCalendar' WHERE module_id = 102 AND name = 'Holiday Calendar';
UPDATE ohrm_screen SET action_url = 'payrollSalaryComponents' WHERE module_id = 102 AND name = 'Salary Components';
UPDATE ohrm_screen SET action_url = 'payrollAttendanceRules' WHERE module_id = 102 AND name = 'Attendance Rules';
UPDATE ohrm_screen SET action_url = 'payrollOvertimeRules' WHERE module_id = 102 AND name = 'Overtime Rules';
UPDATE ohrm_screen SET action_url = 'payrollTaxSlabs' WHERE module_id = 102 AND name = 'Tax Slabs';
UPDATE ohrm_screen SET action_url = 'payrollFinancialYear' WHERE module_id = 102 AND name = 'Financial Year';

-- But wait — the admin screens have module_id=102 (payroll) but their routes are under /admin/
-- The URL would be built as /payroll/payrollSalaryComponents instead of /admin/payrollSalaryComponents
-- These admin screens need module_id for the 'admin' module instead
-- Let's check what admin module_id is first:
SELECT id, name FROM ohrm_module WHERE name = 'admin';

-- Verify payroll screens
SELECT id, name, action_url FROM ohrm_screen WHERE module_id = 102 ORDER BY id;
