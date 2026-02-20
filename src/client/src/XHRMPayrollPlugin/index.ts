import SalaryComponents from '@/XHRMPayrollPlugin/pages/admin/SalaryComponents.vue';
import AttendanceRules from '@/XHRMPayrollPlugin/pages/admin/AttendanceRules.vue';
import OvertimeRules from '@/XHRMPayrollPlugin/pages/admin/OvertimeRules.vue';
import TaxSlabs from '@/XHRMPayrollPlugin/pages/admin/TaxSlabs.vue';
import FinancialYear from '@/XHRMPayrollPlugin/pages/admin/FinancialYear.vue';
import PayrollGenerate from '@/XHRMPayrollPlugin/pages/payroll/PayrollGenerate.vue';
import PayrollRunsList from '@/XHRMPayrollPlugin/pages/payroll/PayrollRunsList.vue';
import PayrollApproveList from '@/XHRMPayrollPlugin/pages/payroll/PayrollApproveList.vue';
import PayrollApproveDetail from '@/XHRMPayrollPlugin/pages/payroll/PayrollApproveDetail.vue';
import EmployeePayslips from '@/XHRMPayrollPlugin/pages/payslip/EmployeePayslips.vue';
import PayslipDetail from '@/XHRMPayrollPlugin/pages/payslip/PayslipDetail.vue';
import MyPayslips from '@/XHRMPayrollPlugin/pages/payslip/MyPayslips.vue';
import Loans from '@/XHRMPayrollPlugin/pages/loans/Loans.vue';
import HolidayCalendar from '@/XHRMPayrollPlugin/pages/HolidayCalendar.vue';

export default {
  'payroll-salary-components': SalaryComponents,
  'payroll-attendance-rules': AttendanceRules,
  'payroll-overtime-rules': OvertimeRules,
  'payroll-tax-slabs': TaxSlabs,
  'payroll-financial-year': FinancialYear,
  'payroll-generate': PayrollGenerate,
  'payroll-runs-list': PayrollRunsList,
  'payroll-approve-list': PayrollApproveList,
  'payroll-approve-detail': PayrollApproveDetail,
  'payroll-employee-payslips': EmployeePayslips,
  'payroll-payslip-detail': PayslipDetail,
  'payroll-my-payslips': MyPayslips,
  'payroll-loans': Loans,
  'payroll-holiday-calendar': HolidayCalendar,
};
