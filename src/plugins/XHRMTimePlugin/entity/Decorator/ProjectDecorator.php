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
use XHRM\Entity\Customer;
use XHRM\Entity\Employee;
use XHRM\Entity\Project;

class ProjectDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var Project
     */
    protected Project $project;

    /**
     * @param  Project  $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @return Project
     */
    protected function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param  int  $id
     */
    public function setCustomerById(int $id): void
    {
        $customer = $this->getReference(Customer::class, $id);
        $this->getProject()->setCustomer($customer);
    }

    /**
     * @param  int[]  $empNumbers
     */
    public function setProjectAdminsByEmpNumbers(array $empNumbers)
    {
        foreach ($empNumbers as $empNumber) {
            $projectAdmin = $this->getReference(Employee::class, $empNumber);
            $this->addProjectAdmin($projectAdmin);
        }
    }

    /**
     * @param  Employee  $employee
     */
    private function addProjectAdmin(Employee $employee): void
    {
        $projectAdmins = $this->getProject()->getProjectAdmins();
        if ($projectAdmins->contains($employee)) {
            return;
        }
        $projectAdmins[] = $employee;
    }

    public function removeProjectAdmins(): void
    {
        $projectAdmins = $this->getProject()->getProjectAdmins();
        foreach ($projectAdmins as $projectAdmin) {
            $projectAdmins->removeElement($projectAdmin);
        }
    }
}

