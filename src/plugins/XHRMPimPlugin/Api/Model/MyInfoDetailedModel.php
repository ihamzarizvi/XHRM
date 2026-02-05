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

namespace XHRM\Pim\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\Employee;

/**
 * @OA\Schema(
 *     schema="Pim-MyInfoDetailedModel",
 *     type="object",
 *     @OA\Property(property="lastName", description="My last name", type="string"),
 *     @OA\Property(property="firstName", description="My first name", type="string"),
 *     @OA\Property(property="middleName", description="My middle name", type="string"),
 *     @OA\Property(property="employeeId", description="My employee ID", type="string"),
 *     @OA\Property(property="terminationId", description="My termination ID", type="integer", nullable=true),
 *     @OA\Property(
 *         property="jobTitle",
 *         type="object",
 *         @OA\Property(property="id", description="The numerical ID of my job title", type="integer"),
 *         @OA\Property(property="title", description="The title of my job", type="string"),
 *         @OA\Property(property="isDeleted", description="The deleted status of my job title", type="boolean")
 *     ),
 *     @OA\Property(
 *         property="subunit",
 *         type="object",
 *         @OA\Property(property="id", description="The numerical ID of my subunit", type="integer"),
 *         @OA\Property(property="name", description="The name of my subunit", type="string")
 *     )
 * )
 */
class MyInfoDetailedModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param Employee $employee
     */
    public function __construct(Employee $employee)
    {
        $this->setEntity($employee);
        $this->setFilters(
            [
                'empNumber',
                'lastName',
                'firstName',
                'middleName',
                'employeeId',
                ['getEmployeeTerminationRecord', 'getId'],
                ['getJobTitle', 'getId'],
                ['getJobTitle', 'getJobTitleName'],
                ['getJobTitle', 'isDeleted'],
                ['getSubDivision', 'getId'],
                ['getSubDivision', 'getName'],
            ]
        );
        $this->setAttributeNames(
            [
                'empNumber',
                'lastName',
                'firstName',
                'middleName',
                'employeeId',
                'terminationId',
                ['jobTitle', 'id'],
                ['jobTitle', 'title'],
                ['jobTitle', 'isDeleted'],
                ['subunit', 'id'],
                ['subunit', 'name'],
            ]
        );
    }
}

