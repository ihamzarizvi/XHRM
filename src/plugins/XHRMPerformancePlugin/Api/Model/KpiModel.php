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

namespace XHRM\Performance\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\Kpi;
use XHRM\Performance\Traits\Service\KpiServiceTrait;

/**
 * @OA\Schema(
 *     schema="Performance-KpiModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(
 *         property="jobTitle",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="deleted", type="boolean"),
 *     ),
 *     @OA\Property(property="minRating", type="integer"),
 *     @OA\Property(property="maxRating", type="integer"),
 *     @OA\Property(property="isDefault", type="boolean"),
 *     @OA\Property(property="deletable", type="boolean")
 * )
 */
class KpiModel implements Normalizable
{
    use ModelTrait {
        ModelTrait::toArray as entityToArray;
    }
    use KpiServiceTrait;

    public function __construct(Kpi $kpi)
    {
        $this->setEntity($kpi);
        $this->setFilters(
            [
                'id',
                'title',
                ['getJobTitle', 'getId'],
                ['getJobTitle', 'getJobTitleName'],
                ['getJobTitle', 'isDeleted'],
                'minRating',
                'maxRating',
                ['isDefaultKpi'],
            ]
        );
        $this->setAttributeNames(
            [
                'id',
                'title',
                ['jobTitle', 'id'],
                ['jobTitle', 'name'],
                ['jobTitle', 'deleted'],
                'minRating',
                'maxRating',
                'isDefault',
            ]
        );
    }

    public function toArray(): array
    {
        $deletable = $this->getKpiService()->getKpiDao()->isKpiDeletable(
            $this->getEntity()->getId()
        );
        $result = $this->entityToArray();
        $result['deletable'] = $deletable;
        return $result;
    }
}

