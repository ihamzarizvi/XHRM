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

namespace XHRM\Claim\Api\Model;

use OpenApi\Annotations as OA;
use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\ClaimEvent;

/**
 * @OA\Schema(
 *     schema="Claim-ClaimEventModel",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="boolean",
 *     )
 * )
 */
class ClaimEventModel implements Normalizable
{
    use ModelTrait;

    public function __construct(ClaimEvent $claimEvent)
    {
        $this->setEntity($claimEvent);
        $this->setFilters(
            [
                'id',
                'name',
                'description',
                'status',
            ]
        );
        $this->setAttributeNames(
            [
                'id',
                'name',
                'description',
                'status'
            ]
        );
    }
}
