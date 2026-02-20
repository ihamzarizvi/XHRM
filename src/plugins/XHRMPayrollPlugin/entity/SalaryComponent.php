<?php

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_salary_component")
 * @ORM\Entity
 */
class SalaryComponent
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100)
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=20, unique=true)
     */
    private string $code;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=10)
     */
    private string $type;

    /**
     * @var string
     * @ORM\Column(name="calculation_type", type="string", length=20, options={"default": "fixed"})
     */
    private string $calculationType = 'fixed';

    /**
     * @var string|null
     * @ORM\Column(name="default_value", type="decimal", precision=12, scale=2, nullable=true)
     */
    private ?string $defaultValue = '0.00';

    /**
     * @var string|null
     * @ORM\Column(name="formula", type="string", length=255, nullable=true)
     */
    private ?string $formula = null;

    /**
     * @var bool
     * @ORM\Column(name="is_taxable", type="boolean", options={"default": true})
     */
    private bool $isTaxable = true;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean", options={"default": true})
     */
    private bool $isActive = true;

    /**
     * @var int
     * @ORM\Column(name="sort_order", type="integer", options={"default": 0})
     */
    private int $sortOrder = 0;

    /**
     * @var string
     * @ORM\Column(name="applies_to", type="string", length=20, options={"default": "all"})
     */
    private string $appliesTo = 'all';

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function setCode(string $code): void
    {
        $this->code = $code;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    public function getCalculationType(): string
    {
        return $this->calculationType;
    }
    public function setCalculationType(string $calculationType): void
    {
        $this->calculationType = $calculationType;
    }
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }
    public function setDefaultValue(?string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }
    public function getFormula(): ?string
    {
        return $this->formula;
    }
    public function setFormula(?string $formula): void
    {
        $this->formula = $formula;
    }
    public function isTaxable(): bool
    {
        return $this->isTaxable;
    }
    public function setIsTaxable(bool $isTaxable): void
    {
        $this->isTaxable = $isTaxable;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
    public function getAppliesTo(): string
    {
        return $this->appliesTo;
    }
    public function setAppliesTo(string $appliesTo): void
    {
        $this->appliesTo = $appliesTo;
    }
}
