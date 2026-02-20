<?php

namespace XHRM\Payroll\Migration;

use Doctrine\DBAL\Schema\Schema;
use XHRM\Core\Migration\AbstractMigration;

class Version20260220000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // 1. Salary Components
        $this->addSql("CREATE TABLE xhrm_salary_component (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL UNIQUE,
            type ENUM('earning', 'deduction') NOT NULL,
            calculation_type ENUM('fixed', 'percentage', 'formula', 'auto') DEFAULT 'fixed',
            default_value DECIMAL(12,2) DEFAULT 0.00,
            formula VARCHAR(255) NULL,
            is_taxable BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            applies_to ENUM('all', 'monthly', 'hourly', 'contract') DEFAULT 'all'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 2. Attendance Rules
        $this->addSql("CREATE TABLE xhrm_attendance_rule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            grace_period_minutes INT DEFAULT 15,
            half_day_hours DECIMAL(4,2) DEFAULT 4.00,
            lates_per_absent INT DEFAULT 3,
            working_days VARCHAR(50) DEFAULT '[1,2,3,4,5,6]',
            is_default BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 3. Overtime Rules
        $this->addSql("CREATE TABLE xhrm_overtime_rule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('weekday', 'weekend', 'holiday') NOT NULL,
            rate_multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.50,
            min_hours_before_ot DECIMAL(4,2) NULL,
            max_ot_hours_per_day DECIMAL(4,2) DEFAULT 4.00,
            is_active BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 4. Financial Year
        $this->addSql("CREATE TABLE xhrm_financial_year (
            id INT AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(20) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('active', 'closed') DEFAULT 'active'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 5. Tax Slabs
        $this->addSql("CREATE TABLE xhrm_tax_slab (
            id INT AUTO_INCREMENT PRIMARY KEY,
            financial_year_id INT NOT NULL,
            min_income DECIMAL(14,2) NOT NULL,
            max_income DECIMAL(14,2) NULL,
            tax_rate DECIMAL(5,2) NOT NULL,
            fixed_amount DECIMAL(14,2) DEFAULT 0.00,
            FOREIGN KEY (financial_year_id) REFERENCES xhrm_financial_year(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 6. Holiday Calendar
        $this->addSql("CREATE TABLE xhrm_holiday (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            date DATE NOT NULL,
            is_recurring BOOLEAN DEFAULT FALSE,
            is_half_day BOOLEAN DEFAULT FALSE,
            applies_to ENUM('all', 'department') DEFAULT 'all',
            department_id INT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 7. Payroll Run
        $this->addSql("CREATE TABLE xhrm_payroll_run (
            id INT AUTO_INCREMENT PRIMARY KEY,
            period_type ENUM('monthly','biweekly','weekly','contract','hourly') NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            status ENUM('draft','pending_approval','approved','rejected','paid') DEFAULT 'draft',
            generated_by INT NOT NULL,
            generated_at DATETIME NOT NULL,
            approved_by INT NULL,
            approved_at DATETIME NULL,
            rejection_note TEXT NULL,
            total_gross DECIMAL(14,2) DEFAULT 0.00,
            total_deductions DECIMAL(14,2) DEFAULT 0.00,
            total_net DECIMAL(14,2) DEFAULT 0.00,
            employee_count INT DEFAULT 0,
            currency_id VARCHAR(6) NOT NULL DEFAULT 'PKR',
            FOREIGN KEY (generated_by) REFERENCES hs_hr_employee(emp_number),
            FOREIGN KEY (approved_by) REFERENCES hs_hr_employee(emp_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 8. Payslip
        $this->addSql("CREATE TABLE xhrm_payslip (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payroll_run_id INT NOT NULL,
            emp_number INT NOT NULL,
            pay_period_type VARCHAR(20) NOT NULL,
            basic_salary DECIMAL(12,2) NOT NULL,
            gross_salary DECIMAL(12,2) NOT NULL,
            total_deductions DECIMAL(12,2) DEFAULT 0.00,
            net_salary DECIMAL(12,2) NOT NULL,
            currency_id VARCHAR(6) NOT NULL DEFAULT 'PKR',
            total_working_days INT NOT NULL,
            days_present INT DEFAULT 0,
            days_absent INT DEFAULT 0,
            days_leave INT DEFAULT 0,
            days_half INT DEFAULT 0,
            late_count INT DEFAULT 0,
            overtime_hours DECIMAL(6,2) DEFAULT 0.00,
            overtime_amount DECIMAL(12,2) DEFAULT 0.00,
            tax_amount DECIMAL(12,2) DEFAULT 0.00,
            status ENUM('generated','approved','emailed','viewed') DEFAULT 'generated',
            emailed_at DATETIME NULL,
            viewed_at DATETIME NULL,
            FOREIGN KEY (payroll_run_id) REFERENCES xhrm_payroll_run(id) ON DELETE CASCADE,
            FOREIGN KEY (emp_number) REFERENCES hs_hr_employee(emp_number),
            UNIQUE KEY unique_run_emp (payroll_run_id, emp_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 9. Payslip Items
        $this->addSql("CREATE TABLE xhrm_payslip_item (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payslip_id INT NOT NULL,
            component_id INT NULL,
            name VARCHAR(100) NOT NULL,
            type ENUM('earning', 'deduction') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            FOREIGN KEY (payslip_id) REFERENCES xhrm_payslip(id) ON DELETE CASCADE,
            FOREIGN KEY (component_id) REFERENCES xhrm_salary_component(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // 10. Employee Loans
        $this->addSql("CREATE TABLE xhrm_employee_loan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_number INT NOT NULL,
            loan_type ENUM('advance', 'loan') NOT NULL,
            description VARCHAR(255) NULL,
            total_amount DECIMAL(12,2) NOT NULL,
            monthly_deduction DECIMAL(12,2) NOT NULL,
            remaining_amount DECIMAL(12,2) NOT NULL,
            start_date DATE NOT NULL,
            status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
            FOREIGN KEY (emp_number) REFERENCES hs_hr_employee(emp_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Register Payroll Module in ohrm_module
        $this->addSql("INSERT INTO ohrm_module (name, status, display_name) VALUES ('payroll', 1, 'Payroll Module')");

        // Add Finance Manager user role
        $this->addSql("INSERT INTO ohrm_user_role (name, display_name, is_assignable, is_predefined) VALUES ('FinanceManager', 'Finance Manager', 1, 1)");

        // Insert default attendance rule
        $this->addSql("INSERT INTO xhrm_attendance_rule (name, grace_period_minutes, half_day_hours, lates_per_absent, working_days, is_default)
            VALUES ('Default Rule', 15, 4.00, 3, '[1,2,3,4,5,6]', 1)");

        // Insert default overtime rules
        $this->addSql("INSERT INTO xhrm_overtime_rule (name, type, rate_multiplier, max_ot_hours_per_day, is_active) VALUES
            ('Weekday Overtime', 'weekday', 1.50, 4.00, 1),
            ('Weekend Overtime', 'weekend', 2.00, 8.00, 1),
            ('Holiday Overtime', 'holiday', 2.00, 8.00, 1)");

        // Insert default salary components
        $this->addSql("INSERT INTO xhrm_salary_component (name, code, type, calculation_type, default_value, formula, is_taxable, is_active, sort_order) VALUES
            ('Basic Salary', 'BASIC', 'earning', 'fixed', 0, NULL, 1, 1, 1),
            ('House Rent Allowance', 'HRA', 'earning', 'percentage', 45, 'basic * 0.45', 1, 1, 2),
            ('Transport Allowance', 'TRANSPORT', 'earning', 'fixed', 5000, NULL, 0, 1, 3),
            ('Medical Allowance', 'MEDICAL', 'earning', 'fixed', 3000, NULL, 0, 1, 4),
            ('Overtime', 'OT', 'earning', 'auto', 0, NULL, 1, 1, 5),
            ('Absent Deduction', 'ABSENT', 'deduction', 'auto', 0, NULL, 0, 1, 10),
            ('Late Deduction', 'LATE', 'deduction', 'auto', 0, NULL, 0, 1, 11),
            ('Income Tax', 'TAX', 'deduction', 'auto', 0, NULL, 0, 1, 12),
            ('EOBI', 'EOBI', 'deduction', 'fixed', 370, NULL, 0, 1, 13),
            ('Loan Deduction', 'LOAN', 'deduction', 'auto', 0, NULL, 0, 1, 14)");

        // Insert default financial year
        $this->addSql("INSERT INTO xhrm_financial_year (label, start_date, end_date, status) VALUES ('2025-2026', '2025-07-01', '2026-06-30', 'active')");

        // Insert Pakistan FBR tax slabs for 2025-2026
        $this->addSql("INSERT INTO xhrm_tax_slab (financial_year_id, min_income, max_income, tax_rate, fixed_amount) VALUES
            (1, 0, 600000, 0.00, 0),
            (1, 600001, 1200000, 2.50, 0),
            (1, 1200001, 2400000, 12.50, 15000),
            (1, 2400001, 3600000, 22.50, 165000),
            (1, 3600001, 6000000, 27.50, 435000),
            (1, 6000001, NULL, 35.00, 1095000)");

        // Add Payroll menu items
        $this->addSql("SET @payroll_module_id = (SELECT id FROM ohrm_module WHERE name = 'payroll')");

        // Get max menu_order for top-level items
        $this->addSql("SET @max_order = (SELECT COALESCE(MAX(`order`), 0) FROM ohrm_menu_item WHERE level = 1)");

        // Main Payroll menu
        $this->addSql("INSERT INTO ohrm_menu_item (menu_title, screen_id, parent_id, level, `order`, status, module_id)
            VALUES ('Payroll', NULL, NULL, 1, @max_order + 1, 1, @payroll_module_id)");
        $this->addSql("SET @payroll_menu_id = LAST_INSERT_ID()");

        // Sub menu items  
        $this->addSql("INSERT INTO ohrm_menu_item (menu_title, screen_id, parent_id, level, `order`, status, module_id) VALUES
            ('Generate Payroll', NULL, @payroll_menu_id, 2, 1, 1, @payroll_module_id),
            ('Payroll Runs', NULL, @payroll_menu_id, 2, 2, 1, @payroll_module_id),
            ('Approve Payroll', NULL, @payroll_menu_id, 2, 3, 1, @payroll_module_id),
            ('Employee Payslips', NULL, @payroll_menu_id, 2, 4, 1, @payroll_module_id),
            ('Loans & Advances', NULL, @payroll_menu_id, 2, 5, 1, @payroll_module_id),
            ('My Payslips', NULL, @payroll_menu_id, 2, 6, 1, @payroll_module_id),
            ('Holiday Calendar', NULL, @payroll_menu_id, 2, 7, 1, @payroll_module_id)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS xhrm_payslip_item");
        $this->addSql("DROP TABLE IF EXISTS xhrm_payslip");
        $this->addSql("DROP TABLE IF EXISTS xhrm_payroll_run");
        $this->addSql("DROP TABLE IF EXISTS xhrm_employee_loan");
        $this->addSql("DROP TABLE IF EXISTS xhrm_tax_slab");
        $this->addSql("DROP TABLE IF EXISTS xhrm_financial_year");
        $this->addSql("DROP TABLE IF EXISTS xhrm_holiday");
        $this->addSql("DROP TABLE IF EXISTS xhrm_overtime_rule");
        $this->addSql("DROP TABLE IF EXISTS xhrm_attendance_rule");
        $this->addSql("DROP TABLE IF EXISTS xhrm_salary_component");
        $this->addSql("DELETE FROM ohrm_menu_item WHERE module_id = (SELECT id FROM ohrm_module WHERE name = 'payroll')");
        $this->addSql("DELETE FROM ohrm_module WHERE name = 'payroll'");
        $this->addSql("DELETE FROM ohrm_user_role WHERE name = 'FinanceManager'");
    }
}
