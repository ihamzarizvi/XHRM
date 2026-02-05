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
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Interview;
use XHRM\Framework\Http\Request;
use XHRM\Recruitment\Traits\Service\CandidateServiceTrait;

class InterviewAttachmentsController extends AbstractVueController implements CapableViewController
{
    use ConfigServiceTrait;
    use CandidateServiceTrait;
    use UserRoleManagerTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $interviewId = $request->attributes->getInt('interviewId');
        $interview = $this->getCandidateService()
            ->getCandidateDao()
            ->getInterviewById($interviewId);
        if (!$interview instanceof Interview) {
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        $component = new Component('view-interview-attachments');
        $component->addProp(new Prop('interview-id', Prop::TYPE_NUMBER, $interviewId));
        $component->addProp(
            new Prop('max-file-size', Prop::TYPE_NUMBER, $this->getConfigService()->getMaxAttachmentSize())
        );
        $component->addProp(
            new Prop('allowed-file-types', Prop::TYPE_ARRAY, $this->getConfigService()->getAllowedFileTypes())
        );
        $this->setComponent($component);
    }

    public function isCapable(Request $request): bool
    {
        $interviewId = $request->attributes->getInt('interviewId');
        if (!$this->getUserRoleManager()->isEntityAccessible(Interview::class, $interviewId)) {
            return false;
        }
        return true;
    }
}

