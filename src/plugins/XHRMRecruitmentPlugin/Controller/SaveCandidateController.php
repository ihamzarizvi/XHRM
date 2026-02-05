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

namespace XHRM\Recruitment\Controller;

use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\Controller\VueComponentPermissionTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Candidate;
use XHRM\Entity\CandidateVacancy;
use XHRM\Framework\Http\Request;
use XHRM\Recruitment\Service\RecruitmentAttachmentService;
use XHRM\Recruitment\Traits\Service\CandidateServiceTrait;

class SaveCandidateController extends AbstractVueController implements CapableViewController
{
    use CandidateServiceTrait;
    use ConfigServiceTrait;
    use UserRoleManagerTrait;
    use VueComponentPermissionTrait;
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $id = $request->attributes->getInt('id');

            if (is_null($this->getCandidateService()->getCandidateDao()->getCandidateById($id))) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
            $component = new Component('view-candidate-profile');
            $candidateVacancy = $this->getCandidateService()->getCandidateDao()->getCandidateVacancyByCandidateId($id);
            $updatable = true;
            if ($candidateVacancy instanceof CandidateVacancy) {
                $rolesToExclude = [];
                $hiringManager = $candidateVacancy->getVacancy()->getHiringManager();
                if ($hiringManager !== null) {
                    $hiringManagerEmpNumber = $candidateVacancy->getVacancy()->getHiringManager()->getEmpNumber();
                    if ($hiringManagerEmpNumber !== $this->getAuthUser()->getEmpNumber()) {
                        $rolesToExclude = ['HiringManager', 'Interviewer'];
                    }
                }
                $updatable = $this->getUserRoleManager()->isEntityAccessible(
                    Candidate::class,
                    $id,
                    null,
                    $rolesToExclude
                );
            }
            $component->addProp(new Prop('updatable', Prop::TYPE_BOOLEAN, $updatable));
            $component->addProp(new Prop('candidate-id', Prop::TYPE_NUMBER, $id));
        } else {
            $component = new Component('save-candidate');
        }

        $component->addProp(
            new Prop('max-file-size', Prop::TYPE_NUMBER, $this->getConfigService()->getMaxAttachmentSize())
        );
        $component->addProp(
            new Prop(
                'allowed-file-types',
                Prop::TYPE_ARRAY,
                RecruitmentAttachmentService::ALLOWED_CANDIDATE_ATTACHMENT_FILE_TYPES
            )
        );
        $this->setComponent($component);
    }

    /**
     * @throws RequestForwardableException
     */
    public function isCapable(Request $request): bool
    {
        if ($request->attributes->has('id')) {
            $id = $request->attributes->getInt('id');

            if (is_null($this->getCandidateService()->getCandidateDao()->getCandidateById($id))) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
            if (!$this->getUserRoleManager()->isEntityAccessible(Candidate::class, $id)) {
                return false;
            }
            return true;
        } elseif (!$this->getUserRoleManager()->getDataGroupPermissions(['recruitment_candidates'])->canCreate()) {
            return false;
        } else {
            return true;
        }
    }
}

