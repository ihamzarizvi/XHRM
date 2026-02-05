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

namespace XHRM\Recruitment\Controller;

use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Candidate;
use XHRM\Entity\CandidateHistory;
use XHRM\Entity\Interview;
use XHRM\Entity\Vacancy;
use XHRM\Framework\Http\Request;
use XHRM\Recruitment\Dto\CandidateActionHistory;
use XHRM\Recruitment\Traits\Service\CandidateServiceTrait;

class WorkflowActionHistoryController extends AbstractVueController implements CapableViewController
{
    use UserRoleManagerTrait;
    use CandidateServiceTrait;
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('view-action-history');
        $candidateId = $request->attributes->getInt('candidateId');
        $historyId = $request->attributes->getInt('historyId');

        $candidateHistory = $this->getCandidateService()
            ->getCandidateDao()->
            getCandidateHistoryRecordByCandidateIdAndHistoryId($candidateId, $historyId);

        if ($candidateHistory instanceof CandidateHistory && $candidateHistory->getInterview() instanceof Interview) {
            $rolesToExclude = [];
            $hiringManagerEmpNumber = $candidateHistory->getVacancy()->getHiringManager()->getEmpNumber();
            if ($hiringManagerEmpNumber !== $this->getAuthUser()->getEmpNumber()) {
                $rolesToExclude = ['HiringManager', 'Interviewer'];
            }
            $editable = $this->getUserRoleManager()->isEntityAccessible(
                Candidate::class,
                $candidateId,
                null,
                $rolesToExclude
            );
            $component->addProp(new Prop('editable', Prop::TYPE_BOOLEAN, $editable));
        }

        $component->addProp(new Prop('candidate-id', Prop::TYPE_NUMBER, $candidateId));
        $component->addProp(new Prop('history-id', Prop::TYPE_NUMBER, $historyId));
        $this->setComponent($component);
    }

    public function isCapable(
        Request $request
    ): bool {
        if ($request->attributes->has('candidateId') && $request->attributes->has('historyId')) {
            $candidateId = $request->attributes->getInt('candidateId');
            $historyId = $request->attributes->getInt('historyId');

            $candidateHistory = $this->getCandidateService()
                ->getCandidateDao()
                ->getCandidateHistoryRecordByCandidateIdAndHistoryId($candidateId, $historyId);
            if (!$candidateHistory instanceof CandidateHistory) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
            if (!$this->getUserRoleManager()->isEntityAccessible(Candidate::class, $candidateId)) {
                return false;
            }
            if (!$this->getUserRoleManager()->isEntityAccessible(CandidateHistory::class, $historyId)) {
                return false;
            }
            if ($candidateHistory->getVacancy() instanceof Vacancy) {
                $rolesToExclude = [];
                $hiringManagerEmpNumber = $candidateHistory->getVacancy()->getHiringManager()->getEmpNumber();
                if ($hiringManagerEmpNumber !== $this->getAuthUser()->getEmpNumber()) {
                    $rolesToExclude = ['HiringManager'];
                }
                $accessibleActionHistoryIds = $this->getUserRoleManager()->getAccessibleEntityIds(
                    CandidateActionHistory::class,
                    null,
                    null,
                    $rolesToExclude
                );
                if (!in_array($candidateHistory->getAction(), $accessibleActionHistoryIds)) {
                    return false;
                }
                $currentVacancyId = $this->getCandidateService()
                    ->getCandidateDao()
                    ->getCurrentVacancyIdByCandidateId($candidateId);
                if ($currentVacancyId != $candidateHistory->getVacancy()->getId()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
