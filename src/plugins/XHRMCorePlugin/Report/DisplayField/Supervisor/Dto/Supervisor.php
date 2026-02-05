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

namespace XHRM\Core\Report\DisplayField\Supervisor\Dto;

use XHRM\Core\Report\DisplayField\NormalizableDTO;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\ReportingMethod;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;

class Supervisor extends NormalizableDTO
{
    use EntityManagerHelperTrait;
    use EmployeeServiceTrait;

    private ?int $empNumber = null;

    public function __construct(?int $empNumber)
    {
        $this->empNumber = $empNumber;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $fields): array
    {
        /** @var Employee $employee */
        $employee = $this->getReference(Employee::class, $this->empNumber);
        return $this->normalizeArray($employee->getSupervisors(), $fields);
    }

    /**
     * @inheritDoc
     * @param Employee $item
     */
    protected function callGetterOnItem($item, string $field, array $getter): ?string
    {
        if ($field === 'supReportingMethod') {
            $reportingMethod = $this->getEmployeeService()
                ->getEmployeeDao()
                ->getReportingMethod($this->empNumber, $item->getEmpNumber());
            if ($reportingMethod instanceof ReportingMethod) {
                return $reportingMethod->getName();
            }
            return null;
        }
        return parent::callGetterOnItem($item, $field, $getter);
    }

    /**
     * @inheritDoc
     */
    protected function getFieldGetterMap(): array
    {
        return [
            'supervisorFirstName' => ['getFirstName'],
            'supervisorLastName' => ['getLastName'],
            'supReportingMethod' => [],
            'supervisorId' => ['getEmpNumber'],
        ];
    }
}

