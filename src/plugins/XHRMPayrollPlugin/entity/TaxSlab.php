<?php

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_tax_slab")
 * @ORM\Entity
 */
class TaxSlab
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var FinancialYear
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\FinancialYear")
     * @ORM\JoinColumn(name="financial_year_id", referencedColumnName="id")
     */
    private FinancialYear $financialYear;

    /**
     * @var string
     * @ORM\Column(name="min_income", type="decimal", precision=14, scale=2)
     */
    private string $minIncome;

    /**
     * @var string|null
     * @ORM\Column(name="max_income", type="decimal", precision=14, scale=2, nullable=true)
     */
    private ?string $maxIncome = null;

    /**
     * @var string
     * @ORM\Column(name="tax_rate", type="decimal", precision=5, scale=2)
     */
    private string $taxRate;

    /**
     * @var string
     * @ORM\Column(name="fixed_amount", type="decimal", precision=14, scale=2, options={"default": "0.00"})
     */
    private string $fixedAmount = '0.00';

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getFinancialYear(): FinancialYear
    {
        return $this->financialYear;
    }
    public function setFinancialYear(FinancialYear $financialYear): void
    {
        $this->financialYear = $financialYear;
    }
    public function getMinIncome(): string
    {
        return $this->minIncome;
    }
    public function setMinIncome(string $minIncome): void
    {
        $this->minIncome = $minIncome;
    }
    public function getMaxIncome(): ?string
    {
        return $this->maxIncome;
    }
    public function setMaxIncome(?string $maxIncome): void
    {
        $this->maxIncome = $maxIncome;
    }
    public function getTaxRate(): string
    {
        return $this->taxRate;
    }
    public function setTaxRate(string $taxRate): void
    {
        $this->taxRate = $taxRate;
    }
    public function getFixedAmount(): string
    {
        return $this->fixedAmount;
    }
    public function setFixedAmount(string $fixedAmount): void
    {
        $this->fixedAmount = $fixedAmount;
    }
}
