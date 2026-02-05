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

namespace XHRM\Performance\Api\Traits;

use XHRM\Core\Authorization\Dto\ResourcePermission;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\PerformanceTrackerLog;

trait PerformanceTrackerPermissionTrait
{
    use UserRoleManagerTrait;

    /**
     * @param PerformanceTrackerLog $performanceTrackerLog
     * @return ResourcePermission
     */
    protected function getTrackerLogPermission(PerformanceTrackerLog $performanceTrackerLog): ResourcePermission
    {
        $self = $this->getUserRoleManagerHelper()
            ->isSelfByEmpNumber($performanceTrackerLog->getEmployee()->getEmpNumber());
        return $this->getUserRoleManager()->getDataGroupPermissions(
            'performance_tracker_log',
            [],
            [],
            $self,
            []
        );
    }
}

