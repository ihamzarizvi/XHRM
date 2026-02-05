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

namespace XHRM\Buzz\Dto;

use DateTime;
use XHRM\Core\Dto\FilterParams;

class EmployeeAnniversarySearchFilterParams extends FilterParams
{
    /**
     * @var string
     */
    protected string $thisYear;

    /**
     * @var DateTime
     */
    protected DateTime $nextDate;

    /**
     * @var int
     */
    protected int $dateDiffMin;

    /**
     * @var int
     */
    protected int $dateDiffMax;

    /**
     * @return string
     */
    public function getThisYear(): string
    {
        return $this->thisYear;
    }

    /**
     * @param string $thisYear
     */
    public function setThisYear(string $thisYear): void
    {
        $this->thisYear = $thisYear;
    }

    /**
     * @return DateTime
     */
    public function getNextDate(): DateTime
    {
        return $this->nextDate;
    }

    /**
     * @param DateTime $nextDate
     */
    public function setNextDate(DateTime $nextDate): void
    {
        $this->nextDate = $nextDate;
    }

    /**
     * @return int
     */
    public function getDateDiffMin(): int
    {
        return $this->dateDiffMin;
    }

    /**
     * @param int $dateDiffMin
     */
    public function setDateDiffMin(int $dateDiffMin): void
    {
        $this->dateDiffMin = $dateDiffMin;
    }

    /**
     * @return int
     */
    public function getDateDiffMax(): int
    {
        return $this->dateDiffMax;
    }

    /**
     * @param int $dateDiffMax
     */
    public function setDateDiffMax(int $dateDiffMax): void
    {
        $this->dateDiffMax = $dateDiffMax;
    }
}

