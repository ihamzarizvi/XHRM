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

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\Leave;
use XHRM\Entity\LeaveComment;
use XHRM\Entity\LeaveType;
use XHRM\Leave\Dto\LeaveDuration;
use XHRM\Leave\Traits\Service\LeaveRequestServiceTrait;
use XHRM\ORM\ListSorter;

class LeaveDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;
    use LeaveRequestServiceTrait;

    /**
     * @var Leave
     */
    private Leave $leave;

    /**
     * @param Leave $leave
     */
    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
    }

    /**
     * @return Leave
     */
    protected function getLeave(): Leave
    {
        return $this->leave;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getLeave()->setEmployee($employee);
    }

    /**
     * @param int $id
     */
    public function setLeaveTypeById(int $id): void
    {
        /** @var LeaveType|null $leaveType */
        $leaveType = $this->getReference(LeaveType::class, $id);
        $this->getLeave()->setLeaveType($leaveType);
    }

    /**
     * @return string e.g. ['Pending Approval', 'Scheduled', 'Taken', 'Rejected', 'Cancelled']
     */
    public function getLeaveStatus(): string
    {
        return ucwords(strtolower($this->getLeaveStatusName()));
    }

    /**
     * @return string e.g. ['PENDING APPROVAL', 'SCHEDULED', 'TAKEN', 'REJECTED', 'CANCELLED']
     */
    public function getLeaveStatusName(): string
    {
        return $this->getLeaveRequestService()->getLeaveStatusNameByStatus($this->getLeave()->getStatus());
    }

    /**
     * @return string Y-m-d date
     */
    public function getLeaveDate(): string
    {
        return $this->getDateTimeHelper()->formatDate($this->getLeave()->getDate());
    }

    /**
     * @return string|null
     */
    public function getLeaveDuration(): ?string
    {
        $type = $this->getLeave()->getDurationType();
        if (isset(LeaveDuration::DURATION_MAP[$type])) {
            return LeaveDuration::DURATION_MAP[$type];
        }
        return null;
    }

    /**
     * @return string|null H:i format
     */
    public function getStartTime(): ?string
    {
        if ($this->getLeave()->getDurationType() !== Leave::DURATION_TYPE_SPECIFY_TIME) {
            return null;
        }
        return $this->getDateTimeHelper()->formatDateTimeToTimeString($this->getLeave()->getStartTime());
    }

    /**
     * @return string|null H:i format
     */
    public function getEndTime(): ?string
    {
        if ($this->getLeave()->getDurationType() !== Leave::DURATION_TYPE_SPECIFY_TIME) {
            return null;
        }
        return $this->getDateTimeHelper()->formatDateTimeToTimeString($this->getLeave()->getEndTime());
    }

    /**
     * @return LeaveComment|null
     */
    public function getLastComment(): ?LeaveComment
    {
        return $this->getRepository(LeaveComment::class)
            ->findOneBy(['leave' => $this->getLeave()->getId()], ['createdAt' => ListSorter::DESCENDING]);
    }
}

