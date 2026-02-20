<?php

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_attendance_rule")
 * @ORM\Entity
 */
class AttendanceRule
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
     * @var int
     * @ORM\Column(name="grace_period_minutes", type="integer", options={"default": 15})
     */
    private int $gracePeriodMinutes = 15;

    /**
     * @var string
     * @ORM\Column(name="half_day_hours", type="decimal", precision=4, scale=2, options={"default": "4.00"})
     */
    private string $halfDayHours = '4.00';

    /**
     * @var int
     * @ORM\Column(name="lates_per_absent", type="integer", options={"default": 3})
     */
    private int $latesPerAbsent = 3;

    /**
     * @var string|null
     * @ORM\Column(name="working_days", type="string", length=50, nullable=true, options={"default": "[1,2,3,4,5,6]"})
     */
    private ?string $workingDays = '[1,2,3,4,5,6]';

    /**
     * @var bool
     * @ORM\Column(name="is_default", type="boolean", options={"default": true})
     */
    private bool $isDefault = true;

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
    public function getGracePeriodMinutes(): int
    {
        return $this->gracePeriodMinutes;
    }
    public function setGracePeriodMinutes(int $gracePeriodMinutes): void
    {
        $this->gracePeriodMinutes = $gracePeriodMinutes;
    }
    public function getHalfDayHours(): string
    {
        return $this->halfDayHours;
    }
    public function setHalfDayHours(string $halfDayHours): void
    {
        $this->halfDayHours = $halfDayHours;
    }
    public function getLatesPerAbsent(): int
    {
        return $this->latesPerAbsent;
    }
    public function setLatesPerAbsent(int $latesPerAbsent): void
    {
        $this->latesPerAbsent = $latesPerAbsent;
    }
    public function getWorkingDays(): ?string
    {
        return $this->workingDays;
    }
    public function setWorkingDays(?string $workingDays): void
    {
        $this->workingDays = $workingDays;
    }
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }
}
