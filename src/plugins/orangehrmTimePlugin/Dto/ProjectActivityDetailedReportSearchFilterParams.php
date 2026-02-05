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

namespace XHRM\Time\Dto;

class ProjectActivityDetailedReportSearchFilterParams extends ProjectReportSearchFilterParams
{
    public const ALLOWED_SORT_FIELDS = ['employee.lastName'];

    /**
     * @var int|null
     */
    private ?int $projectActivityId = null;

    public function __construct()
    {
        parent::__construct();
        $this->setSortField('employee.lastName');
    }

    /**
     * @return int|null
     */
    public function getProjectActivityId(): ?int
    {
        return $this->projectActivityId;
    }

    /**
     * @param int|null $projectActivityId
     */
    public function setProjectActivityId(?int $projectActivityId): void
    {
        $this->projectActivityId = $projectActivityId;
    }
}
