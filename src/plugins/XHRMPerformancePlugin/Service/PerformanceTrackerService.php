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

namespace XHRM\Performance\Service;

use XHRM\Entity\PerformanceTracker;
use XHRM\Performance\Dao\PerformanceTrackerDao;
use XHRM\Performance\Exception\PerformanceTrackerServiceException;

class PerformanceTrackerService
{
    /**
     * @var PerformanceTrackerDao|null
     */
    private ?PerformanceTrackerDao $performanceTrackerDao = null;

    /**
     * @return PerformanceTrackerDao
     */
    public function getPerformanceTrackerDao(): PerformanceTrackerDao
    {
        if (!($this->performanceTrackerDao instanceof PerformanceTrackerDao)) {
            $this->performanceTrackerDao = new PerformanceTrackerDao();
        }
        return $this->performanceTrackerDao;
    }

    /**
     * @param PerformanceTracker $performanceTracker
     * @param array $reviewerEmpNumbers
     * @return void
     * @throws PerformanceTrackerServiceException
     */
    public function updateTracker(PerformanceTracker $performanceTracker, array $reviewerEmpNumbers): void
    {
        if (
            !$this->getPerformanceTrackerDao()
                ->isTrackerOwnerEditable($performanceTracker->getId())
        ) {
            throw PerformanceTrackerServiceException::cannotEditEmployeeWithLogs();
        }
        $this->getPerformanceTrackerDao()->updatePerformanceTracker($performanceTracker, $reviewerEmpNumbers);
    }
}

