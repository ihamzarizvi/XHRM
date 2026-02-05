<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace XHRM\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ohrm_leave_type")
 * @ORM\Entity
 */
class LeaveType
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", length=4)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private string $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="exclude_in_reports_if_no_entitlement", type="boolean", options={"default" : 0})
     */
    private bool $situational = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean", options={"default" : 0})
     */
    private bool $deleted = false;

    /**
     * @var OperationalCountry|null
     *
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\OperationalCountry")
     * @ORM\JoinColumn(name="operational_country_id", referencedColumnName="id", nullable=true)
     */
    private ?OperationalCountry $operationalCountry = null;

    /**
     * @var Collection|LeaveEntitlement[]
     *
     * @ORM\OneToMany(targetEntity="XHRM\Entity\LeaveEntitlement", mappedBy="leaveType")
     */
    private iterable $leaveEntitlement;

    public function __construct()
    {
        $this->leaveEntitlement = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isSituational(): bool
    {
        return $this->situational;
    }

    /**
     * @param bool $situational
     */
    public function setSituational(bool $situational): void
    {
        $this->situational = $situational;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return OperationalCountry|null
     */
    public function getOperationalCountry(): ?OperationalCountry
    {
        return $this->operationalCountry;
    }

    /**
     * @param OperationalCountry|null $operationalCountry
     */
    public function setOperationalCountry(?OperationalCountry $operationalCountry): void
    {
        $this->operationalCountry = $operationalCountry;
    }
}

