<?php

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_payslip_item")
 * @ORM\Entity
 */
class PayslipItem
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var Payslip
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Payslip")
     * @ORM\JoinColumn(name="payslip_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Payslip $payslip;

    /**
     * @var SalaryComponent|null
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\SalaryComponent")
     * @ORM\JoinColumn(name="component_id", referencedColumnName="id", nullable=true)
     */
    private ?SalaryComponent $component = null;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100)
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=10)
     */
    private string $type;

    /**
     * @var string
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    private string $amount;

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getPayslip(): Payslip
    {
        return $this->payslip;
    }
    public function setPayslip(Payslip $payslip): void
    {
        $this->payslip = $payslip;
    }
    public function getComponent(): ?SalaryComponent
    {
        return $this->component;
    }
    public function setComponent(?SalaryComponent $component): void
    {
        $this->component = $component;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    public function getAmount(): string
    {
        return $this->amount;
    }
    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }
}
