<?php

namespace XHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_employee_loan")
 * @ORM\Entity
 */
class EmployeeLoan
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var Employee
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Employee")
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")
     */
    private Employee $employee;

    /**
     * @var string
     * @ORM\Column(name="loan_type", type="string", length=20)
     */
    private string $loanType;

    /**
     * @var string|null
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @var string
     * @ORM\Column(name="total_amount", type="decimal", precision=12, scale=2)
     */
    private string $totalAmount;

    /**
     * @var string
     * @ORM\Column(name="monthly_deduction", type="decimal", precision=12, scale=2)
     */
    private string $monthlyDeduction;

    /**
     * @var string
     * @ORM\Column(name="remaining_amount", type="decimal", precision=12, scale=2)
     */
    private string $remainingAmount;

    /**
     * @var DateTime
     * @ORM\Column(name="start_date", type="date")
     */
    private DateTime $startDate;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=20, options={"default": "active"})
     */
    private string $status = 'active';

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getEmployee(): Employee
    {
        return $this->employee;
    }
    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }
    public function getLoanType(): string
    {
        return $this->loanType;
    }
    public function setLoanType(string $loanType): void
    {
        $this->loanType = $loanType;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }
    public function setTotalAmount(string $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }
    public function getMonthlyDeduction(): string
    {
        return $this->monthlyDeduction;
    }
    public function setMonthlyDeduction(string $monthlyDeduction): void
    {
        $this->monthlyDeduction = $monthlyDeduction;
    }
    public function getRemainingAmount(): string
    {
        return $this->remainingAmount;
    }
    public function setRemainingAmount(string $remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
