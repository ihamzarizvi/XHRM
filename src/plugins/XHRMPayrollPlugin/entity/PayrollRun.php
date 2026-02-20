<?php

namespace XHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_payroll_run")
 * @ORM\Entity
 */
class PayrollRun
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(name="period_type", type="string", length=20)
     */
    private string $periodType;

    /**
     * @var DateTime
     * @ORM\Column(name="period_start", type="date")
     */
    private DateTime $periodStart;

    /**
     * @var DateTime
     * @ORM\Column(name="period_end", type="date")
     */
    private DateTime $periodEnd;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=20, options={"default": "draft"})
     */
    private string $status = self::STATUS_DRAFT;

    /**
     * @var Employee
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Employee")
     * @ORM\JoinColumn(name="generated_by", referencedColumnName="emp_number")
     */
    private Employee $generatedBy;

    /**
     * @var DateTime
     * @ORM\Column(name="generated_at", type="datetime")
     */
    private DateTime $generatedAt;

    /**
     * @var Employee|null
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Employee")
     * @ORM\JoinColumn(name="approved_by", referencedColumnName="emp_number", nullable=true)
     */
    private ?Employee $approvedBy = null;

    /**
     * @var DateTime|null
     * @ORM\Column(name="approved_at", type="datetime", nullable=true)
     */
    private ?DateTime $approvedAt = null;

    /**
     * @var string|null
     * @ORM\Column(name="rejection_note", type="text", nullable=true)
     */
    private ?string $rejectionNote = null;

    /**
     * @var string
     * @ORM\Column(name="total_gross", type="decimal", precision=14, scale=2, options={"default": "0.00"})
     */
    private string $totalGross = '0.00';

    /**
     * @var string
     * @ORM\Column(name="total_deductions", type="decimal", precision=14, scale=2, options={"default": "0.00"})
     */
    private string $totalDeductions = '0.00';

    /**
     * @var string
     * @ORM\Column(name="total_net", type="decimal", precision=14, scale=2, options={"default": "0.00"})
     */
    private string $totalNet = '0.00';

    /**
     * @var int
     * @ORM\Column(name="employee_count", type="integer", options={"default": 0})
     */
    private int $employeeCount = 0;

    /**
     * @var string
     * @ORM\Column(name="currency_id", type="string", length=6, options={"default": "PKR"})
     */
    private string $currencyId = 'PKR';

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getPeriodType(): string
    {
        return $this->periodType;
    }
    public function setPeriodType(string $periodType): void
    {
        $this->periodType = $periodType;
    }
    public function getPeriodStart(): DateTime
    {
        return $this->periodStart;
    }
    public function setPeriodStart(DateTime $periodStart): void
    {
        $this->periodStart = $periodStart;
    }
    public function getPeriodEnd(): DateTime
    {
        return $this->periodEnd;
    }
    public function setPeriodEnd(DateTime $periodEnd): void
    {
        $this->periodEnd = $periodEnd;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function getGeneratedBy(): Employee
    {
        return $this->generatedBy;
    }
    public function setGeneratedBy(Employee $generatedBy): void
    {
        $this->generatedBy = $generatedBy;
    }
    public function getGeneratedAt(): DateTime
    {
        return $this->generatedAt;
    }
    public function setGeneratedAt(DateTime $generatedAt): void
    {
        $this->generatedAt = $generatedAt;
    }
    public function getApprovedBy(): ?Employee
    {
        return $this->approvedBy;
    }
    public function setApprovedBy(?Employee $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }
    public function getApprovedAt(): ?DateTime
    {
        return $this->approvedAt;
    }
    public function setApprovedAt(?DateTime $approvedAt): void
    {
        $this->approvedAt = $approvedAt;
    }
    public function getRejectionNote(): ?string
    {
        return $this->rejectionNote;
    }
    public function setRejectionNote(?string $rejectionNote): void
    {
        $this->rejectionNote = $rejectionNote;
    }
    public function getTotalGross(): string
    {
        return $this->totalGross;
    }
    public function setTotalGross(string $totalGross): void
    {
        $this->totalGross = $totalGross;
    }
    public function getTotalDeductions(): string
    {
        return $this->totalDeductions;
    }
    public function setTotalDeductions(string $totalDeductions): void
    {
        $this->totalDeductions = $totalDeductions;
    }
    public function getTotalNet(): string
    {
        return $this->totalNet;
    }
    public function setTotalNet(string $totalNet): void
    {
        $this->totalNet = $totalNet;
    }
    public function getEmployeeCount(): int
    {
        return $this->employeeCount;
    }
    public function setEmployeeCount(int $employeeCount): void
    {
        $this->employeeCount = $employeeCount;
    }
    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }
    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }
}
