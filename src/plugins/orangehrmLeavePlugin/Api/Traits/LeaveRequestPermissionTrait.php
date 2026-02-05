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

namespace XHRM\Leave\Api\Traits;

use LogicException;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\LeaveRequest;

trait LeaveRequestPermissionTrait
{
    use UserRoleManagerTrait;

    /**
     * @param LeaveRequest $leaveRequest
     */
    protected function checkLeaveRequestAccessible(LeaveRequest $leaveRequest): void
    {
        if (!$this instanceof Endpoint) {
            throw new LogicException(
                self::class . ' should use in instanceof' . Endpoint::class
            );
        }
        $empNumber = $leaveRequest->getEmployee()->getEmpNumber();
        if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($empNumber)) {
            throw $this->getForbiddenException();
        }
    }
}
