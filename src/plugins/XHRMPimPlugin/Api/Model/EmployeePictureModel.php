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
use XHRM\Entity\EmpPicture;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeePictureModel",
 *     type="object",
 *     @OA\Property(property="empNumber", type="integer"),
 *     @OA\Property(property="filename", type="string"),
 *     @OA\Property(property="fileType", type="string"),
 *     @OA\Property(property="size", type="integer"),
 *     @OA\Property(property="width", type="integer"),
 *     @OA\Property(property="height", type="integer")
 * )
 */
class EmployeePictureModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param EmpPicture $empPicture
     */
    public function __construct(EmpPicture $empPicture)
    {
        $this->setEntity($empPicture);
        $this->setFilters(
            [
                ['getEmployee', 'getEmpNumber'],
                'filename',
                'fileType',
                'size',
                'width',
                'height'
            ]
        );
        $this->setAttributeNames(
            [
                'empNumber',
            ]
        );
    }
}

