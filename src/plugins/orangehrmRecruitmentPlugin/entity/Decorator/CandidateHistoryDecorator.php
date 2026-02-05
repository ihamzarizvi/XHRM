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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\Candidate;
use XHRM\Entity\CandidateHistory;
use XHRM\Entity\Employee;
use XHRM\Entity\Interview;
use XHRM\Entity\Vacancy;
use XHRM\Recruitment\Service\CandidateService;

class CandidateHistoryDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    /**
     * @var CandidateHistory
     */
    private CandidateHistory $candidateHistory;

    /**
     * @param CandidateHistory $candidateHistory
     */
    public function __construct(CandidateHistory $candidateHistory)
    {
        $this->candidateHistory = $candidateHistory;
    }

    /**
     * @param int $id
     */
    public function setCandidateById(int $id): void
    {
        $candidate = $this->getReference(Candidate::class, $id);
        $this->candidateHistory->setCandidate($candidate);
    }

    /**
     * @param int $id
     */
    public function setVacancyById(int $id): void
    {
        $vacancy = $this->getReference(Vacancy::class, $id);
        $this->candidateHistory->setVacancy($vacancy);
    }

    /**
     * @param int $id
     */
    public function setInterviewByInterviewId(int $id): void
    {
        $interview = $this->getReference(Interview::class, $id);
        $this->candidateHistory->setInterview($interview);
    }

    /**
     * @param int $id
     */
    public function setPerformedBy(int $id): void
    {
        $performedBy = $this->getReference(Employee::class, $id);
        $this->candidateHistory->setPerformedBy($performedBy);
    }

    /**
     * @return string
     */
    public function getCandidateHistoryAction(): string
    {
        $actionId = $this->candidateHistory->getAction();
        $candidateHistoryMap = array_replace(CandidateService::STATUS_MAP, CandidateService::OTHER_ACTIONS_MAP);
        return ucwords(strtolower($candidateHistoryMap[$actionId]));
    }

    /**
     * @return string
     */
    public function getPerformedDate(): string
    {
        $date = $this->candidateHistory->getPerformedDate();
        return $this->getDateTimeHelper()->formatDate($date);
    }
}
