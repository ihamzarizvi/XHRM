<?php

namespace XHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="xhrm_financial_year")
 * @ORM\Entity
 */
class FinancialYear
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
     * @ORM\Column(name="label", type="string", length=20)
     */
    private string $label;

    /**
     * @var DateTime
     * @ORM\Column(name="start_date", type="date")
     */
    private DateTime $startDate;

    /**
     * @var DateTime
     * @ORM\Column(name="end_date", type="date")
     */
    private DateTime $endDate;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=10, options={"default": "active"})
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
    public function getLabel(): string
    {
        return $this->label;
    }
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
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
