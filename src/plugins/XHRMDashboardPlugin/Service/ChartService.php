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

namespace XHRM\Dashboard\Service;

use XHRM\Dashboard\Dao\ChartDao;
use XHRM\Dashboard\Dto\EmployeeDistributionByLocation;
use XHRM\Dashboard\Dto\EmployeeDistributionBySubunit;
use XHRM\Dashboard\Dto\SubunitCountPair;

class ChartService
{
    /**
     * @var ChartDao
     */
    private ChartDao $chartDao;

    /**
     * @return ChartDao
     */
    public function getChartDao(): ChartDao
    {
        return $this->chartDao ??= new ChartDao();
    }

    /**
     * @param int $limit
     * @return EmployeeDistributionBySubunit
     */
    public function getEmployeeDistributionBySubunit(int $limit = 8): EmployeeDistributionBySubunit
    {
        $subunitCountPairs = $this->getChartDao()
            ->getEmployeeDistributionBySubunit();
        usort(
            $subunitCountPairs,
            static function (SubunitCountPair $x, SubunitCountPair $y) {
                return ($x->getCount() < $y->getCount()) ? 1 : -1;
            }
        );

        $totalSubunitCount = count($subunitCountPairs);
        $otherArray = [];
        if ($totalSubunitCount > $limit + 1) {
            $otherArray = array_slice($subunitCountPairs, $limit);
            $subunitCountPairs = array_slice($subunitCountPairs, 0, $limit);
        }

        $otherCount = 0;
        foreach ($otherArray as $subunitCountPair) {
            $otherCount += $subunitCountPair->getCount();
        }

        return new EmployeeDistributionBySubunit(
            $subunitCountPairs,
            $otherCount,
            $totalSubunitCount,
            $this->getChartDao()->getUnassignedEmployeeCount(),
            $limit
        );
    }

    /**
     * @param array $locationEmployeeCounts
     * @return int
     */
    public function getLocationUnassignedEmployeeCount(array $locationEmployeeCounts): int
    {
        $totalActiveEmployee = $this->getChartDao()->getTotalActiveEmployeeCount();

        $assignedEmployeeCount = 0;
        foreach ($locationEmployeeCounts as $locationEmployeeCount) {
            $assignedEmployeeCount += $locationEmployeeCount->getEmployeeCount();
        }
        return $totalActiveEmployee - $assignedEmployeeCount;
    }

    /**
     * @param int $limit
     * @return EmployeeDistributionByLocation
     */
    public function getEmployeeDistributionByLocation(int $limit = 8): EmployeeDistributionByLocation
    {
        $locationEmployeeCount = $this->getChartDao()->getEmployeeDistributionByLocation();
        $unassignedEmployeeCount = $this->getLocationUnassignedEmployeeCount($locationEmployeeCount);

        $totalLocationCount = count($locationEmployeeCount);
        $otherArray = [];
        if ($totalLocationCount > $limit + 1) {
            $otherArray = array_slice($locationEmployeeCount, $limit);
            $locationEmployeeCount = array_slice($locationEmployeeCount, 0, $limit);
        }

        $otherCount = 0;
        foreach ($otherArray as $locationCountPair) {
            $otherCount += $locationCountPair->getEmployeeCount();
        }

        return new EmployeeDistributionByLocation(
            $locationEmployeeCount,
            $otherCount,
            $totalLocationCount,
            $unassignedEmployeeCount,
            $limit,
        );
    }
}

