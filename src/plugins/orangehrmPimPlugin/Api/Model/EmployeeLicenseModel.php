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
use XHRM\Entity\EmployeeLicense;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeeLicenseModel",
 *     type="object",
 *     @OA\Property(property="licenseNo", description="The employee's license number", type="string"),
 *     @OA\Property(property="issuedDate", description="The issue date of the employee's license", type="string", format="date"),
 *     @OA\Property(property="expiryDate", description="The expiry date of the employee's license", type="string", format="date"),
 *     @OA\Property(property="license", type="object",
 *         @OA\Property(property="id", description="The numerical ID of the license", type="integer"),
 *         @OA\Property(property="name", description="The name of the license", type="string")
 *     )
 * )
 */
class EmployeeLicenseModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param EmployeeLicense $employeeLicense
     */
    public function __construct(EmployeeLicense $employeeLicense)
    {
        $this->setEntity($employeeLicense);
        $this->setFilters(
            [
                'licenseNo',
                ['getDecorator', 'getLicenseIssuedDate'],
                ['getDecorator', 'getLicenseExpiryDate'],
                ['getLicense', 'getId'],
                ['getLicense', 'getName']
            ]
        );
        $this->setAttributeNames(
            [
                'licenseNo',
                'issuedDate',
                'expiryDate',
                ['license', 'id'],
                ['license', 'name']
            ]
        );
    }
}
