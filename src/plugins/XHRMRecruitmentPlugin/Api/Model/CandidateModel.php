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

namespace XHRM\Recruitment\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\Candidate;

/**
 * @OA\Schema(
 *     schema="Recruitment-CandidateModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="firstName", type="string"),
 *     @OA\Property(property="middleName", type="string"),
 *     @OA\Property(property="lastName", type="string"),
 *     @OA\Property(property="email", type="string")
 * )
 */
class CandidateModel implements Normalizable
{
    use ModelTrait;

    public function __construct(Candidate $candidate)
    {
        $this->setEntity($candidate);
        $this->setFilters([
            'id',
            'firstName',
            'middleName',
            'lastName',
            'email',
        ]);

        $this->setAttributeNames([
            'id',
            'firstName',
            'middleName',
            'lastName',
            'email',
        ]);
    }
}

