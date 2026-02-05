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

namespace XHRM\Recruitment\Dto;

use XHRM\Core\Dto\FilterParams;
use XHRM\ORM\ListSorter;

class CandidateHistorySearchFilterParams extends FilterParams
{
    public const ALLOWED_SORT_FIELDS = [
        'candidateHistory.performedDate',
    ];

    /**
     * @var array
     */
    private array $actionIds;

    /**
     * @var int
     */
    protected int $candidateId;

    public function __construct()
    {
        $this->setSortField('candidateHistory.performedDate');
        $this->setSortOrder(ListSorter::DESCENDING);
    }

    /**
     * @return int
     */
    public function getCandidateId(): int
    {
        return $this->candidateId;
    }

    /**
     * @param int $candidateId
     */
    public function setCandidateId(int $candidateId): void
    {
        $this->candidateId = $candidateId;
    }

    /**
     * @return array
     */
    public function getActionIds(): array
    {
        return $this->actionIds;
    }

    /**
     * @param array $actionIds
     */
    public function setActionIds(array $actionIds): void
    {
        $this->actionIds = $actionIds;
    }
}

