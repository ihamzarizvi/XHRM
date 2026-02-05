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
use XHRM\Entity\LeaveRequest;
use XHRM\Entity\LeaveRequestComment;
use XHRM\Entity\User;

class LeaveRequestCommentDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    /**
     * @var LeaveRequestComment
     */
    private LeaveRequestComment $leaveRequestComment;

    /**
     * @param LeaveRequestComment $leaveRequestComment
     */
    public function __construct(LeaveRequestComment $leaveRequestComment)
    {
        $this->leaveRequestComment = $leaveRequestComment;
    }

    /**
     * @return LeaveRequestComment
     */
    public function getLeaveRequestComment(): LeaveRequestComment
    {
        return $this->leaveRequestComment;
    }

    /**
     * @param int $empNumber
     */
    public function setCreatedByEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getLeaveRequestComment()->setCreatedByEmployee($employee);
    }

    /**
     * @param int $userId
     */
    public function setCreatedByUserById(int $userId): void
    {
        /** @var User|null $user */
        $user = $this->getReference(User::class, $userId);
        $this->getLeaveRequestComment()->setCreatedBy($user);
    }

    /**
     * @param int $id
     */
    public function setLeaveRequestById(int $id): void
    {
        /** @var LeaveRequest|null $leaveRequest */
        $leaveRequest = $this->getReference(LeaveRequest::class, $id);
        $this->getLeaveRequestComment()->setLeaveRequest($leaveRequest);
    }

    /**
     * @return string Y-m-d
     */
    public function getCreatedAtDate(): string
    {
        $dateTime = $this->getLeaveRequestComment()->getCreatedAt();
        return $this->getDateTimeHelper()->formatDate($dateTime);
    }

    /**
     * @return string H:i
     */
    public function getCreatedAtTime(): string
    {
        $dateTime = $this->getLeaveRequestComment()->getCreatedAt();
        return $this->getDateTimeHelper()->formatDateTimeToTimeString($dateTime);
    }
}

