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

namespace XHRM\Leave\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\LeavePeriodHistory;

/**
 * @OA\Schema(
 *     schema="Leave-LeavePeriodHistoryModel",
 *     type="object",
 *     @OA\Property(property="startMonth", type="integer"),
 *     @OA\Property(property="startDay", type="integer"),
 *     @OA\Property(property="createdAt", type="number")
 * )
 */
class LeavePeriodHistoryModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param LeavePeriodHistory $leavePeriodHistory
     */
    public function __construct(LeavePeriodHistory $leavePeriodHistory)
    {
        $this->setEntity($leavePeriodHistory);
        $this->setFilters(
            [
                'startMonth',
                'startDay',
                ['getDecorator', 'getCreatedAt'],
            ]
        );
        $this->setAttributeNames(
            [
                'startMonth',
                'startDay',
                'createdAt',
            ]
        );
    }
}

