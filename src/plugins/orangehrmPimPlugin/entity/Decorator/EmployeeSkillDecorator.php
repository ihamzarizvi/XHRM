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
use XHRM\Entity\EmployeeSkill;
use XHRM\Entity\Skill;
use XHRM\Entity\Employee;

class EmployeeSkillDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var EmployeeSkill
     */
    protected EmployeeSkill $employeeSkill;

    /**
     * @param EmployeeSkill $employeeSkill
     */
    public function __construct(EmployeeSkill $employeeSkill)
    {
        $this->employeeSkill = $employeeSkill;
    }

    /**
     * @return EmployeeSkill
     */
    protected function getEmployeeSkill(): EmployeeSkill
    {
        return $this->employeeSkill;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmployeeSkill()->setEmployee($employee);
    }

    /**
     * @param int $skillId
     */
    public function setSkillBySkillId(int $skillId): void
    {
        /** @var Skill|null $skill */
        $skill = $this->getReference(Skill::class, $skillId);
        $this->getEmployeeSkill()->setSkill($skill);
    }
}
