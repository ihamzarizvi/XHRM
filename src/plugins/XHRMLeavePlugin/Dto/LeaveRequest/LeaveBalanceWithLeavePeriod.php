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

namespace XHRM\Leave\Dto\LeaveRequest;

use XHRM\Leave\Dto\LeavePeriod;
use XHRM\Leave\Entitlement\LeaveBalance;

class LeaveBalanceWithLeavePeriod
{
    private LeaveBalance $leaveBalance;

    private ?LeavePeriod $leavePeriod = null;

    /**
     * @param LeaveBalance $leaveBalance
     * @param LeavePeriod|null $leavePeriod
     */
    public function __construct(LeaveBalance $leaveBalance, ?LeavePeriod $leavePeriod = null)
    {
        $this->leaveBalance = $leaveBalance;
        $this->leavePeriod = $leavePeriod;
    }

    /**
     * @return LeaveBalance
     */
    public function getLeaveBalance(): LeaveBalance
    {
        return $this->leaveBalance;
    }

    /**
     * @param LeaveBalance $leaveBalance
     */
    public function setLeaveBalance(LeaveBalance $leaveBalance): void
    {
        $this->leaveBalance = $leaveBalance;
    }

    /**
     * @return LeavePeriod|null
     */
    public function getLeavePeriod(): ?LeavePeriod
    {
        return $this->leavePeriod;
    }

    /**
     * @param LeavePeriod|null $leavePeriod
     */
    public function setLeavePeriod(?LeavePeriod $leavePeriod): void
    {
        $this->leavePeriod = $leavePeriod;
    }
}

