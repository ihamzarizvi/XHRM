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

namespace XHRM\Time\Api\Model;

use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;

/**
 * @OA\Schema(
 *     schema="Time-TotalDurationModel",
 *     type="object",
 *     @OA\Property(property="hours", type="integer"),
 *     @OA\Property(property="minutes", type="string"),
 *     @OA\Property(property="label", type="string"),
 * )
 */
class TotalDurationModel implements Normalizable
{
    use DateTimeHelperTrait;

    private int $duration;

    /**
     * @param int $duration
     */
    public function __construct(int $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $hours = floor($this->duration / 3600);
        $minutes = ($this->duration / 60) % 60;
        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'label' => $this->getDateTimeHelper()->convertSecondsToTimeString($this->duration),
        ];
    }
}

