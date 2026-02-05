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

namespace XHRM\Pim\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\EmployeeImmigrationRecord;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeeImmigrationModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="number", type="string"),
 *     @OA\Property(property="issuedDate", type="string", format="date"),
 *     @OA\Property(property="expiryDate", type="string", format="date"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="reviewDate", type="string", format="date"),
 *     @OA\Property(property="country", type="object",
 *         @OA\Property(property="code", type="string"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="comment", type="string")
 * )
 */
class EmployeeImmigrationModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param EmployeeImmigrationRecord $employeeImmigrationRecord
     */
    public function __construct(EmployeeImmigrationRecord $employeeImmigrationRecord)
    {
        $this->setEntity($employeeImmigrationRecord);
        $this->setFilters(
            [
                'recordId',
                'number',
                ['getDecorator', 'getIssuedDate'],
                ['getDecorator', 'getExpiryDate'],
                'type',
                'status',
                ['getDecorator', 'getReviewDate'],
                'countryCode',
                ['getDecorator', 'getCountryName'],
                'comment',
            ]
        );
        $this->setAttributeNames(
            [
                'id',
                'number',
                'issuedDate',
                'expiryDate',
                'type',
                'status',
                'reviewDate',
                ['country', 'code'],
                ['country', 'name'],
                'comment',
            ]
        );
    }
}
