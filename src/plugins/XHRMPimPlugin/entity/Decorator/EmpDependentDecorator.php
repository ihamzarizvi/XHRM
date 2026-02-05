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
use XHRM\Entity\EmpDependent;
use XHRM\Entity\Employee;

class EmpDependentDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    /**
     * @var EmpDependent
     */
    protected EmpDependent $empDependent;

    /**
     * @param EmpDependent $employee
     */
    public function __construct(EmpDependent $employee)
    {
        $this->empDependent = $employee;
    }

    /**
     * @return EmpDependent
     */
    protected function getEmpDependent(): EmpDependent
    {
        return $this->empDependent;
    }

    /**
     * @return string|null
     */
    public function getDateOfBirth(): ?string
    {
        $date = $this->getEmpDependent()->getDateOfBirth();
        return $this->getDateTimeHelper()->formatDate($date);
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmpDependent()->setEmployee($employee);
    }

    /**
     * @return string|null
     */
    public function getRelationship(): ?string
    {
        if ($this->getEmpDependent()->getRelationshipType() === EmpDependent::RELATIONSHIP_TYPE_OTHER) {
            return $this->getEmpDependent()->getRelationship();
        }
        return ucfirst($this->getEmpDependent()->getRelationshipType());
    }
}

