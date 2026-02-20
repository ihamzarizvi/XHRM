<?php

namespace XHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_payslip")
 * @ORM\Entity
 */
class Payslip
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var PayrollRun
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\PayrollRun")
     * @ORM\JoinColumn(name="payroll_run_id", referencedColumnName="id")
     */
    private PayrollRun $payrollRun;

    /**
     * @var Employee
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Employee")
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")
     */
    private Employee $employee;

    /**
     * @var string
     * @ORM\Column(name="pay_period_type", type="string", length=20)
     */
    private string $payPeriodType;

    /**
     * @var string
     * @ORM\Column(name="basic_salary", type="decimal", precision=12, scale=2)
     */
    private string $basicSalary;

    /**
     * @var string
     * @ORM\Column(name="gross_salary", type="decimal", precision=12, scale=2)
     */
    private string $grossSalary;

    /**
     * @var string
     * @ORM\Column(name="total_deductions", type="decimal", precision=12, scale=2, options={"default": "0.00"})
     */
    private string $totalDeductions = '0.00';

    /**
     * @var string
     * @ORM\Column(name="net_salary", type="decimal", precision=12, scale=2)
     */
    private string $netSalary;

    /**
     * @var string
     * @ORM\Column(name="currency_id", type="string", length=6, options={"default": "PKR"})
     */
    private string $currencyId = 'PKR';

    /**
     * @var int
     * @ORM\Column(name="total_working_days", type="integer")
     */
    private int $totalWorkingDays;

    /**
     * @var int
     * @ORM\Column(name="days_present", type="integer", options={"default": 0})
     */
    private int $daysPresent = 0;

    /**
     * @var int
     * @ORM\Column(name="days_absent", type="integer", options={"default": 0})
     */
    private int $daysAbsent = 0;

    /**
     * @var int
     * @ORM\Column(name="days_leave", type="integer", options={"default": 0})
     */
    private int $daysLeave = 0;

    /**
     * @var int
     * @ORM\Column(name="days_half", type="integer", options={"default": 0})
     */
    private int $daysHalf = 0;

    /**
     * @var int
     * @ORM\Column(name="late_count", type="integer", options={"default": 0})
     */
    private int $lateCount = 0;

    /**
     * @var string
     * @ORM\Column(name="overtime_hours", type="decimal", precision=6, scale=2, options={"default": "0.00"})
     */
    private string $overtimeHours = '0.00';

    /**
     * @var string
     * @ORM\Column(name="overtime_amount", type="decimal", precision=12, scale=2, options={"default": "0.00"})
     */
    private string $overtimeAmount = '0.00';

    /**
     * @var string
     * @ORM\Column(name="tax_amount", type="decimal", precision=12, scale=2, options={"default": "0.00"})
     */
    private string $taxAmount = '0.00';

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=20, options={"default": "generated"})
     */
    private string $status = 'generated';

    /**
     * @var DateTime|null
     * @ORM\Column(name="emailed_at", type="datetime", nullable=true)
     */
    private ?DateTime $emailedAt = null;

    /**
     * @var DateTime|null
     * @ORM\Column(name="viewed_at", type="datetime", nullable=true)
     */
    private ?DateTime $viewedAt = null;

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getPayrollRun(): PayrollRun
    {
        return $this->payrollRun;
    }
    public function setPayrollRun(PayrollRun $payrollRun): void
    {
        $this->payrollRun = $payrollRun;
    }
    public function getEmployee(): Employee
    {
        return $this->employee;
    }
    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }
    public function getPayPeriodType(): string
    {
        return $this->payPeriodType;
    }
    public function setPayPeriodType(string $payPeriodType): void
    {
        $this->payPeriodType = $payPeriodType;
    }
    public function getBasicSalary(): string
    {
        return $this->basicSalary;
    }
    public function setBasicSalary(string $basicSalary): void
    {
        $this->basicSalary = $basicSalary;
    }
    public function getGrossSalary(): string
    {
        return $this->grossSalary;
    }
    public function setGrossSalary(string $grossSalary): void
    {
        $this->grossSalary = $grossSalary;
    }
    public function getTotalDeductions(): string
    {
        return $this->totalDeductions;
    }
    public function setTotalDeductions(string $totalDeductions): void
    {
        $this->totalDeductions = $totalDeductions;
    }
    public function getNetSalary(): string
    {
        return $this->netSalary;
    }
    public function setNetSalary(string $netSalary): void
    {
        $this->netSalary = $netSalary;
    }
    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }
    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }
    public function getTotalWorkingDays(): int
    {
        return $this->totalWorkingDays;
    }
    public function setTotalWorkingDays(int $totalWorkingDays): void
    {
        $this->totalWorkingDays = $totalWorkingDays;
    }
    public function getDaysPresent(): int
    {
        return $this->daysPresent;
    }
    public function setDaysPresent(int $daysPresent): void
    {
        $this->daysPresent = $daysPresent;
    }
    public function getDaysAbsent(): int
    {
        return $this->daysAbsent;
    }
    public function setDaysAbsent(int $daysAbsent): void
    {
        $this->daysAbsent = $daysAbsent;
    }
    public function getDaysLeave(): int
    {
        return $this->daysLeave;
    }
    public function setDaysLeave(int $daysLeave): void
    {
        $this->daysLeave = $daysLeave;
    }
    public function getDaysHalf(): int
    {
        return $this->daysHalf;
    }
    public function setDaysHalf(int $daysHalf): void
    {
        $this->daysHalf = $daysHalf;
    }
    public function getLateCount(): int
    {
        return $this->lateCount;
    }
    public function setLateCount(int $lateCount): void
    {
        $this->lateCount = $lateCount;
    }
    public function getOvertimeHours(): string
    {
        return $this->overtimeHours;
    }
    public function setOvertimeHours(string $overtimeHours): void
    {
        $this->overtimeHours = $overtimeHours;
    }
    public function getOvertimeAmount(): string
    {
        return $this->overtimeAmount;
    }
    public function setOvertimeAmount(string $overtimeAmount): void
    {
        $this->overtimeAmount = $overtimeAmount;
    }
    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }
    public function setTaxAmount(string $taxAmount): void
    {
        $this->taxAmount = $taxAmount;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function getEmailedAt(): ?DateTime
    {
        return $this->emailedAt;
    }
    public function setEmailedAt(?DateTime $emailedAt): void
    {
        $this->emailedAt = $emailedAt;
    }
    public function getViewedAt(): ?DateTime
    {
        return $this->viewedAt;
    }
    public function setViewedAt(?DateTime $viewedAt): void
    {
        $this->viewedAt = $viewedAt;
    }
}
