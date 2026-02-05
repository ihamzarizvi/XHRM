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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\ReportingMethod;
use XHRM\Entity\ReportTo;

class ReportToDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var ReportTo
     */
    protected ReportTo $employeeReportTo;

    /**
     * @param ReportTo $employeeReportTo
     */
    public function __construct(ReportTo $employeeReportTo)
    {
        $this->employeeReportTo = $employeeReportTo;
    }

    /**
     * @return ReportTo
     */
    protected function getEmployeeReportTo(): ReportTo
    {
        return $this->employeeReportTo;
    }

    /**
     * @param int $empNumber
     */
    public function setSupervisorEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmployeeReportTo()->setSupervisor($employee);
    }
    /**
     * @param int $empNumber
     */
    public function setSubordinateEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmployeeReportTo()->setSubordinate($employee);
    }

    /**
     * @param int $reportingMethodId
     */
    public function setReportingMethodByReportingMethodId(int $reportingMethodId): void
    {
        /** @var ReportingMethod|null $reportingMethod */
        $reportingMethod = $this->getReference(ReportingMethod::class, $reportingMethodId);
        $this->getEmployeeReportTo()->setReportingMethod($reportingMethod);
    }
}
