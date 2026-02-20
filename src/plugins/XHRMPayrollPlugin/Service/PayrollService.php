<?php

namespace XHRM\Payroll\Service;

use DateTime;
use XHRM\Entity\PayrollRun;
use XHRM\Entity\Payslip;
use XHRM\Entity\PayslipItem;
use XHRM\Payroll\Dao\PayrollDao;

class PayrollService
{
    private ?PayrollDao $payrollDao = null;

    public function getPayrollDao(): PayrollDao
    {
        if ($this->payrollDao === null) {
            $this->payrollDao = new PayrollDao();
        }
        return $this->payrollDao;
    }

    /**
     * Generate payslips for all active employees in a payroll run.
     * This is the core payroll calculation engine.
     */
    public function generatePayslips(PayrollRun $run): void
    {
        $dao = $this->getPayrollDao();

        // Get all active employees from the existing employee table
        $employees = $this->getActiveEmployees();
        $attendanceRule = $dao->getDefaultAttendanceRule();
        $overtimeRules = $dao->getActiveOvertimeRules();
        $salaryComponents = $dao->getActiveSalaryComponents();
        $holidays = $dao->getHolidaysBetween($run->getPeriodStart(), $run->getPeriodEnd());

        // Get active financial year for tax calculation
        $activeFinancialYear = $dao->getActiveFinancialYear();
        $taxSlabs = $activeFinancialYear
            ? $dao->getTaxSlabsForYear($activeFinancialYear->getId())
            : [];

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        $empCount = 0;

        foreach ($employees as $employee) {
            $empNumber = $employee['emp_number'];

            // Get employee's basic salary from hs_hr_emp_basicsalary
            $basicSalary = $this->getEmployeeBasicSalary($empNumber);
            if ($basicSalary <= 0) {
                continue;
            }

            // Calculate attendance data
            $attendanceData = $this->calculateAttendance(
                $empNumber,
                $run->getPeriodStart(),
                $run->getPeriodEnd(),
                $attendanceRule,
                $holidays
            );

            // Create payslip
            $payslip = new Payslip();
            $payslip->setPayrollRun($run);
            $payslip->getDecorator()->setEmployeeByEmpNumber($empNumber);
            $payslip->setPayPeriodType($run->getPeriodType());
            $payslip->setBasicSalary((string) $basicSalary);
            $payslip->setCurrencyId($run->getCurrencyId());
            $payslip->setTotalWorkingDays($attendanceData['totalWorkingDays']);
            $payslip->setDaysPresent($attendanceData['daysPresent']);
            $payslip->setDaysAbsent($attendanceData['daysAbsent']);
            $payslip->setDaysLeave($attendanceData['daysLeave']);
            $payslip->setDaysHalf($attendanceData['daysHalf']);
            $payslip->setLateCount($attendanceData['lateCount']);
            $payslip->setOvertimeHours((string) $attendanceData['overtimeHours']);
            $payslip->setStatus('generated');

            // Calculate per-day salary
            $perDaySalary = $basicSalary / max($attendanceData['totalWorkingDays'], 1);

            // Calculate earnings
            $grossSalary = 0;
            $totalDeductionsForEmp = 0;

            foreach ($salaryComponents as $component) {
                $amount = 0;
                $componentType = $component->getType();

                if ($componentType === 'earning') {
                    $amount = $this->calculateComponentAmount(
                        $component,
                        $basicSalary,
                        $perDaySalary,
                        $attendanceData
                    );
                    if ($amount > 0) {
                        $this->createPayslipItem($payslip, $component, $amount);
                        $grossSalary += $amount;
                    }
                } elseif ($componentType === 'deduction') {
                    $amount = $this->calculateDeductionAmount(
                        $component,
                        $basicSalary,
                        $perDaySalary,
                        $attendanceData,
                        $empNumber
                    );
                    if ($amount > 0) {
                        $this->createPayslipItem($payslip, $component, $amount);
                        $totalDeductionsForEmp += $amount;
                    }
                }
            }

            // Calculate overtime amount
            $overtimeAmount = $this->calculateOvertimeAmount(
                $perDaySalary,
                $attendanceData['overtimeHours'],
                $overtimeRules
            );
            if ($overtimeAmount > 0) {
                $payslip->setOvertimeAmount((string) $overtimeAmount);
                $grossSalary += $overtimeAmount;
            }

            // Calculate tax
            $annualIncome = $grossSalary * 12;
            $monthlyTax = $this->calculateMonthlyTax($annualIncome, $taxSlabs);
            if ($monthlyTax > 0) {
                $payslip->setTaxAmount((string) $monthlyTax);
                $totalDeductionsForEmp += $monthlyTax;
            }

            // Calculate loan deduction
            $loanDeduction = $this->calculateLoanDeduction($empNumber);
            if ($loanDeduction > 0) {
                $totalDeductionsForEmp += $loanDeduction;
            }

            $netSalary = $grossSalary - $totalDeductionsForEmp;

            $payslip->setGrossSalary((string) $grossSalary);
            $payslip->setTotalDeductions((string) $totalDeductionsForEmp);
            $payslip->setNetSalary((string) max($netSalary, 0));

            $dao->savePayslip($payslip);

            $totalGross += $grossSalary;
            $totalDeductions += $totalDeductionsForEmp;
            $totalNet += max($netSalary, 0);
            $empCount++;
        }

        // Update payroll run totals
        $run->setTotalGross((string) $totalGross);
        $run->setTotalDeductions((string) $totalDeductions);
        $run->setTotalNet((string) $totalNet);
        $run->setEmployeeCount($empCount);
        $dao->savePayrollRun($run);
    }

    /**
     * Calculate the amount for an earning component
     */
    private function calculateComponentAmount($component, float $basicSalary, float $perDaySalary, array $attendance): float
    {
        $calcType = $component->getCalculationType();
        $code = $component->getCode();

        switch ($calcType) {
            case 'fixed':
                if ($code === 'BASIC') {
                    return $basicSalary;
                }
                return (float) $component->getDefaultValue();

            case 'percentage':
                return $basicSalary * ((float) $component->getDefaultValue() / 100);

            case 'auto':
                // Auto-calculated based on code
                if ($code === 'OT') {
                    return 0; // Handled separately by overtime calculation
                }
                return 0;

            case 'formula':
                // Simple formula evaluation (basic * multiplier)
                $formula = $component->getFormula();
                if ($formula && strpos($formula, 'basic') !== false) {
                    $result = str_replace('basic', $basicSalary, $formula);
                    // Safely evaluate simple math expressions
                    return (float) $this->safeEval($result);
                }
                return 0;

            default:
                return 0;
        }
    }

    /**
     * Calculate the amount for a deduction component
     */
    private function calculateDeductionAmount($component, float $basicSalary, float $perDaySalary, array $attendance, int $empNumber): float
    {
        $calcType = $component->getCalculationType();
        $code = $component->getCode();

        switch ($code) {
            case 'ABSENT':
                return $attendance['daysAbsent'] * $perDaySalary;

            case 'LATE':
                // Late deduction is handled through absent conversion (lates / X = absent)
                return 0;

            case 'TAX':
                return 0; // Handled separately

            case 'EOBI':
                return (float) $component->getDefaultValue();

            case 'LOAN':
                return 0; // Handled separately

            default:
                if ($calcType === 'fixed') {
                    return (float) $component->getDefaultValue();
                }
                if ($calcType === 'percentage') {
                    return $basicSalary * ((float) $component->getDefaultValue() / 100);
                }
                return 0;
        }
    }

    /**
     * Calculate overtime pay
     */
    private function calculateOvertimeAmount(float $perDaySalary, float $otHours, array $overtimeRules): float
    {
        if ($otHours <= 0 || empty($overtimeRules)) {
            return 0;
        }

        $perHourSalary = $perDaySalary / 8;
        $defaultMultiplier = 1.5;

        foreach ($overtimeRules as $rule) {
            if ($rule->getType() === 'weekday') {
                $defaultMultiplier = (float) $rule->getRateMultiplier();
                break;
            }
        }

        return $otHours * $perHourSalary * $defaultMultiplier;
    }

    /**
     * Calculate monthly tax using Pakistan FBR tax slabs
     */
    private function calculateMonthlyTax(float $annualIncome, array $taxSlabs): float
    {
        if (empty($taxSlabs)) {
            return 0;
        }

        foreach ($taxSlabs as $slab) {
            $min = (float) $slab->getMinIncome();
            $max = $slab->getMaxIncome() !== null ? (float) $slab->getMaxIncome() : PHP_FLOAT_MAX;

            if ($annualIncome >= $min && $annualIncome <= $max) {
                $taxableAboveMin = $annualIncome - $min;
                $rate = (float) $slab->getTaxRate() / 100;
                $fixedAmount = (float) $slab->getFixedAmount();
                $annualTax = $fixedAmount + ($taxableAboveMin * $rate);
                return round($annualTax / 12, 2);
            }
        }

        return 0;
    }

    /**
     * Calculate loan deduction for an employee
     */
    private function calculateLoanDeduction(int $empNumber): float
    {
        $activeLoans = $this->getPayrollDao()->getActiveLoansForEmployee($empNumber);
        $totalDeduction = 0;

        foreach ($activeLoans as $loan) {
            $totalDeduction += (float) $loan->getMonthlyDeduction();
        }

        return $totalDeduction;
    }

    /**
     * Calculate attendance data for the pay period
     */
    private function calculateAttendance(
        int $empNumber,
        DateTime $periodStart,
        DateTime $periodEnd,
        $attendanceRule,
        array $holidays
    ): array {
        $gracePeriod = $attendanceRule ? $attendanceRule->getGracePeriodMinutes() : 15;
        $halfDayHours = $attendanceRule ? (float) $attendanceRule->getHalfDayHours() : 4;
        $latesPerAbsent = $attendanceRule ? $attendanceRule->getLatesPerAbsent() : 3;
        $workingDaysJson = $attendanceRule ? $attendanceRule->getWorkingDays() : '[1,2,3,4,5,6]';
        $workingDays = json_decode($workingDaysJson, true) ?? [1, 2, 3, 4, 5, 6];

        // Get holiday dates for quick lookup
        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $holidayDates[] = $holiday->getDate()->format('Y-m-d');
        }

        // Count total working days (excluding holidays and off days)
        $totalWorkingDays = 0;
        $current = clone $periodStart;
        while ($current <= $periodEnd) {
            $dayOfWeek = (int) $current->format('N'); // 1=Monday, 7=Sunday
            $dateStr = $current->format('Y-m-d');
            if (in_array($dayOfWeek, $workingDays) && !in_array($dateStr, $holidayDates)) {
                $totalWorkingDays++;
            }
            $current->modify('+1 day');
        }

        // Get attendance records from ohrm_attendance_record
        $attendanceRecords = $this->getAttendanceRecords($empNumber, $periodStart, $periodEnd);

        // Get leave records
        $leaveCount = $this->getApprovedLeaveCount($empNumber, $periodStart, $periodEnd);

        $daysPresent = count($attendanceRecords);
        $lateCount = 0;
        $halfDayCount = 0;
        $overtimeHours = 0;

        foreach ($attendanceRecords as $record) {
            // Check late arrivals
            if (isset($record['punch_in_note']) && strpos($record['punch_in_note'], 'LATE') !== false) {
                $lateCount++;
            }

            // Calculate hours worked
            if (isset($record['total_hours'])) {
                $hoursWorked = (float) $record['total_hours'];
                if ($hoursWorked > 0 && $hoursWorked < $halfDayHours) {
                    $halfDayCount++;
                }
                if ($hoursWorked > 8) {
                    $overtimeHours += ($hoursWorked - 8);
                }
            }
        }

        // Convert lates to absents
        $absentsFromLates = floor($lateCount / max($latesPerAbsent, 1));
        $daysAbsent = max($totalWorkingDays - $daysPresent - $leaveCount, 0) + $absentsFromLates;

        return [
            'totalWorkingDays' => $totalWorkingDays,
            'daysPresent' => $daysPresent,
            'daysAbsent' => (int) $daysAbsent,
            'daysLeave' => $leaveCount,
            'daysHalf' => $halfDayCount,
            'lateCount' => $lateCount,
            'overtimeHours' => round($overtimeHours, 2),
        ];
    }

    /**
     * Get all active employees
     */
    private function getActiveEmployees(): array
    {
        $conn = $this->getPayrollDao()->getEntityManager()->getConnection();
        $sql = "SELECT emp_number, emp_firstname, emp_lastname 
                FROM hs_hr_employee 
                WHERE termination_id IS NULL 
                ORDER BY emp_number";
        return $conn->fetchAllAssociative($sql);
    }

    /**
     * Get employee's basic salary from hs_hr_emp_basicsalary
     */
    private function getEmployeeBasicSalary(int $empNumber): float
    {
        $conn = $this->getPayrollDao()->getEntityManager()->getConnection();
        $sql = "SELECT ebsal_basic_salary FROM hs_hr_emp_basicsalary 
                WHERE emp_number = :empNumber 
                ORDER BY id DESC LIMIT 1";
        $result = $conn->fetchOne($sql, ['empNumber' => $empNumber]);
        return (float) ($result ?: 0);
    }

    /**
     * Get attendance records for an employee in a date range
     */
    private function getAttendanceRecords(int $empNumber, DateTime $start, DateTime $end): array
    {
        $conn = $this->getPayrollDao()->getEntityManager()->getConnection();
        $sql = "SELECT 
                    DATE(punch_in_utc_time) as attendance_date,
                    punch_in_note,
                    TIMESTAMPDIFF(HOUR, punch_in_utc_time, punch_out_utc_time) as total_hours
                FROM ohrm_attendance_record
                WHERE employee_id = :empNumber
                AND DATE(punch_in_utc_time) BETWEEN :start AND :end
                AND punch_out_utc_time IS NOT NULL
                GROUP BY DATE(punch_in_utc_time)";
        return $conn->fetchAllAssociative($sql, [
            'empNumber' => $empNumber,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    /**
     * Get approved leave count for an employee in a date range
     */
    private function getApprovedLeaveCount(int $empNumber, DateTime $start, DateTime $end): int
    {
        $conn = $this->getPayrollDao()->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(*) FROM ohrm_leave
                WHERE emp_number = :empNumber
                AND date BETWEEN :start AND :end
                AND status = 3"; // 3 = approved
        return (int) $conn->fetchOne($sql, [
            'empNumber' => $empNumber,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    /**
     * Create a payslip line item
     */
    private function createPayslipItem(Payslip $payslip, $component, float $amount): void
    {
        $item = new PayslipItem();
        $item->setPayslip($payslip);
        $item->setComponent($component);
        $item->setName($component->getName());
        $item->setType($component->getType());
        $item->setAmount((string) round($amount, 2));
        $this->getPayrollDao()->savePayslipItem($item);
    }

    /**
     * Safely evaluate simple math expressions (basic * 0.45)
     */
    private function safeEval(string $expression): float
    {
        // Only allow numbers, basic operators, spaces, and dots
        $expression = preg_replace('/[^0-9+\-*\/\.\s]/', '', $expression);
        try {
            // Use eval cautiously for simple expressions
            $result = 0;
            eval ('$result = ' . $expression . ';');
            return (float) $result;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
