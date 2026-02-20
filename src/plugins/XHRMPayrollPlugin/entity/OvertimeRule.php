<?php

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_overtime_rule")
 * @ORM\Entity
 */
class OvertimeRule
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
     * @ORM\Column(name="type", type="string", length=20)
     */
    private string $type;

    /**
     * @var string
     * @ORM\Column(name="rate_multiplier", type="decimal", precision=4, scale=2, options={"default": "1.50"})
     */
    private string $rateMultiplier = '1.50';

    /**
     * @var string|null
     * @ORM\Column(name="min_hours_before_ot", type="decimal", precision=4, scale=2, nullable=true)
     */
    private ?string $minHoursBeforeOt = null;

    /**
     * @var string
     * @ORM\Column(name="max_ot_hours_per_day", type="decimal", precision=4, scale=2, options={"default": "4.00"})
     */
    private string $maxOtHoursPerDay = '4.00';

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean", options={"default": true})
     */
    private bool $isActive = true;

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
    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    public function getRateMultiplier(): string
    {
        return $this->rateMultiplier;
    }
    public function setRateMultiplier(string $rateMultiplier): void
    {
        $this->rateMultiplier = $rateMultiplier;
    }
    public function getMinHoursBeforeOt(): ?string
    {
        return $this->minHoursBeforeOt;
    }
    public function setMinHoursBeforeOt(?string $minHoursBeforeOt): void
    {
        $this->minHoursBeforeOt = $minHoursBeforeOt;
    }
    public function getMaxOtHoursPerDay(): string
    {
        return $this->maxOtHoursPerDay;
    }
    public function setMaxOtHoursPerDay(string $maxOtHoursPerDay): void
    {
        $this->maxOtHoursPerDay = $maxOtHoursPerDay;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
