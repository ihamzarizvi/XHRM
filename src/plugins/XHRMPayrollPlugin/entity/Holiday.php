<?php

namespace XHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_holiday")
 * @ORM\Entity
 */
class Holiday
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
     * @var DateTime
     * @ORM\Column(name="date", type="date")
     */
    private DateTime $date;

    /**
     * @var bool
     * @ORM\Column(name="is_recurring", type="boolean", options={"default": false})
     */
    private bool $isRecurring = false;

    /**
     * @var bool
     * @ORM\Column(name="is_half_day", type="boolean", options={"default": false})
     */
    private bool $isHalfDay = false;

    /**
     * @var string
     * @ORM\Column(name="applies_to", type="string", length=20, options={"default": "all"})
     */
    private string $appliesTo = 'all';

    /**
     * @var int|null
     * @ORM\Column(name="department_id", type="integer", nullable=true)
     */
    private ?int $departmentId = null;

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
    public function getDate(): DateTime
    {
        return $this->date;
    }
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }
    public function isRecurring(): bool
    {
        return $this->isRecurring;
    }
    public function setIsRecurring(bool $isRecurring): void
    {
        $this->isRecurring = $isRecurring;
    }
    public function isHalfDay(): bool
    {
        return $this->isHalfDay;
    }
    public function setIsHalfDay(bool $isHalfDay): void
    {
        $this->isHalfDay = $isHalfDay;
    }
    public function getAppliesTo(): string
    {
        return $this->appliesTo;
    }
    public function setAppliesTo(string $appliesTo): void
    {
        $this->appliesTo = $appliesTo;
    }
    public function getDepartmentId(): ?int
    {
        return $this->departmentId;
    }
    public function setDepartmentId(?int $departmentId): void
    {
        $this->departmentId = $departmentId;
    }
}
