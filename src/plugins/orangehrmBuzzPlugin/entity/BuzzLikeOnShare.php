<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.orangehrm.com
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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\Decorator\BuzzLikeOnShareDecorator;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Decorator\DecoratorTrait;

/**
 * @method BuzzLikeOnShareDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_buzz_like_on_share")
 * @ORM\Entity
 */
class BuzzLikeOnShare
{
    use DateTimeHelperTrait;
    use DecoratorTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var BuzzShare
     *
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\BuzzShare")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id")
     */
    private BuzzShare $share;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Employee")
     * @ORM\JoinColumn(name="employee_number", referencedColumnName="emp_number")
     */
    private Employee $employee;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="like_utc_time", type="datetime")
     */
    private DateTime $likedAtUtc;

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
     * @return BuzzShare
     */
    public function getShare(): BuzzShare
    {
        return $this->share;
    }

    /**
     * @param BuzzShare $share
     */
    public function setShare(BuzzShare $share): void
    {
        $this->share = $share;
    }

    /**
     * @return Employee
     */
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     */
    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }

    /**
     * @return DateTime
     */
    public function getLikedAtUtc(): DateTime
    {
        return $this->likedAtUtc;
    }

    public function setLikedAtUtc(): void
    {
        $this->likedAtUtc = $this->getDateTimeHelper()->getNowInUTC();
    }
}
