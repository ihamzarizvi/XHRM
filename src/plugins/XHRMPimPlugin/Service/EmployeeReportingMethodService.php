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

namespace XHRM\Pim\Service;

use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Pim\Dao\EmployeeReportingMethodDao;
use XHRM\Pim\Dto\EmployeeSubordinateSearchFilterParams;
use XHRM\Pim\Dto\EmployeeSupervisorSearchFilterParams;

class EmployeeReportingMethodService
{
    use NormalizerServiceTrait;

    /**
     * @var EmployeeReportingMethodDao|null
     */
    protected ?EmployeeReportingMethodDao $employeeReportingMethodDao = null;

    /**
     * @return EmployeeReportingMethodDao
     */
    public function getEmployeeReportingMethodDao(): EmployeeReportingMethodDao
    {
        if (!$this->employeeReportingMethodDao instanceof EmployeeReportingMethodDao) {
            $this->employeeReportingMethodDao = new EmployeeReportingMethodDao();
        }
        return $this->employeeReportingMethodDao;
    }

    /**
     * @param EmployeeReportingMethodDao|null $employeeReportingMethodDao
     */
    public function setEmployeeReportingMethodDao(?EmployeeReportingMethodDao $employeeReportingMethodDao): void
    {
        $this->employeeReportingMethodDao = $employeeReportingMethodDao;
    }

    /**
     * @param EmployeeSupervisorSearchFilterParams $employeeSupervisorSearchFilterParams
     * @return array
     */
    public function getImmediateSupervisorListForEmployee(EmployeeSupervisorSearchFilterParams $employeeSupervisorSearchFilterParams): array
    {
        return $this->getEmployeeReportingMethodDao()->searchImmediateEmployeeSupervisors($employeeSupervisorSearchFilterParams);
    }

    /**
     * @param EmployeeSupervisorSearchFilterParams $employeeSupervisorSearchFilterParams
     * @return int
     */
    public function getImmediateSupervisorListCountForEmployee(EmployeeSupervisorSearchFilterParams $employeeSupervisorSearchFilterParams): int
    {
        return $this->getEmployeeReportingMethodDao()->getSearchImmediateEmployeeSupervisorsCount($employeeSupervisorSearchFilterParams);
    }

    /**
     * @param EmployeeSubordinateSearchFilterParams $employeeSubordinateSearchFilterParams
     * @return array
     */
    public function getSubordinateListForEmployee(EmployeeSubordinateSearchFilterParams $employeeSubordinateSearchFilterParams): array
    {
        return $this->getEmployeeReportingMethodDao()->searchEmployeeSubordinates($employeeSubordinateSearchFilterParams);
    }

    /**
     * @param EmployeeSubordinateSearchFilterParams $employeeSubordinateSearchFilterParams
     * @return int
     */
    public function getSubordinateListCountForEmployee(EmployeeSubordinateSearchFilterParams $employeeSubordinateSearchFilterParams): int
    {
        return $this->getEmployeeReportingMethodDao()->getSearchEmployeeSubordinatesCount($employeeSubordinateSearchFilterParams);
    }

    public function getAccessibleAndAvailableSupervisorsIdCombinedList(array $accessibleEmpNumbers, array $alreadyAssignedEmpNumbers): array
    {
        return array_values(array_diff($accessibleEmpNumbers, $alreadyAssignedEmpNumbers));
    }

    public function getAlreadyAssignedSupervisorsSubordinatesAndSelfIdCombinedList(array $supervisors, array $subordinates, int $empNumber): array
    {
        return array_merge($supervisors, $subordinates, [$empNumber]);
    }
}

