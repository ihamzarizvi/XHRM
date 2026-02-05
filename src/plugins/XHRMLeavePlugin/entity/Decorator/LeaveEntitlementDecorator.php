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

namespace XHRM\Entity\Decorator;

use DateTime;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\LeaveEntitlement;
use XHRM\Entity\LeaveEntitlementType;
use XHRM\Entity\LeaveType;
use XHRM\Leave\Traits\Service\LeaveEntitlementServiceTrait;

class LeaveEntitlementDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;
    use LeaveEntitlementServiceTrait;

    /**
     * @var LeaveEntitlement
     */
    private LeaveEntitlement $leaveEntitlement;

    /**
     * @param LeaveEntitlement $leaveEntitlement
     */
    public function __construct(LeaveEntitlement $leaveEntitlement)
    {
        $this->leaveEntitlement = $leaveEntitlement;
    }

    /**
     * @return LeaveEntitlement
     */
    protected function getLeaveEntitlement(): LeaveEntitlement
    {
        return $this->leaveEntitlement;
    }

    /**
     * @return float
     */
    public function getAvailableDays(): float
    {
        $available = $this->getLeaveEntitlement()->getNoOfDays();
        $daysUsed = $this->getLeaveEntitlement()->getDaysUsed();

        if (!empty($daysUsed)) {
            $available -= $daysUsed;
        }

        return $available;
    }

    /**
     * @param DateTime $date
     * @return bool
     */
    public function withinPeriod(DateTime $date): bool
    {
        return ($date >= $this->getLeaveEntitlement()->getFromDate()) &&
            ($date <= $this->getLeaveEntitlement()->getToDate());
    }

    /**
     * @param int $id
     */
    public function setEntitlementTypeById(int $id): void
    {
        /** @var LeaveEntitlementType $entitlementType */
        $entitlementType = $this->getReference(LeaveEntitlementType::class, $id);
        $this->getLeaveEntitlement()->setEntitlementType($entitlementType);
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getLeaveEntitlement()->setEmployee($employee);
    }

    /**
     * @param int $id
     */
    public function setLeaveTypeById(int $id): void
    {
        /** @var LeaveType $leaveType */
        $leaveType = $this->getReference(LeaveType::class, $id);
        $this->getLeaveEntitlement()->setLeaveType($leaveType);
    }

    /**
     * @return string
     */
    public function getFromDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getLeaveEntitlement()->getFromDate());
    }

    /**
     * @return string
     */
    public function getToDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getLeaveEntitlement()->getToDate());
    }

    /**
     * @return string|null
     */
    public function getCreditedDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDate($this->getLeaveEntitlement()->getCreditedDate());
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->getLeaveEntitlementService()->isDeletable($this->getLeaveEntitlement());
    }
}

