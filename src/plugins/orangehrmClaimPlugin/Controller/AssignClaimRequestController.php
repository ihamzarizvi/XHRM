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

namespace XHRM\Claim\Controller;

use XHRM\Claim\Traits\Service\ClaimServiceTrait;
use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\ClaimRequest;
use XHRM\Framework\Http\Request;

class AssignClaimRequestController extends AbstractVueController implements CapableViewController
{
    use ClaimServiceTrait;
    use UserRoleManagerTrait;
    use ConfigServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $id = $request->attributes->getInt('id');
        $claimRequest = $this->getClaimService()->getClaimDao()->getClaimRequestById($id);
        $empNumber = $claimRequest->getEmployee()->getEmpNumber();

        if ($this->getUserRoleManagerHelper()->isSelfByEmpNumber($claimRequest->getEmployee()->getEmpNumber())) {
            $this->setResponse($this->redirect("claim/submitClaim/id/$id"));
            return;
        }

        $component = new Component('assign-claim');
        $component->addProp(new Prop('emp-number', Prop::TYPE_NUMBER, $empNumber));
        $component->addProp(new Prop('id', Prop::TYPE_NUMBER, $id));
        $component->addProp(new Prop(
            'allowed-file-types',
            Prop::TYPE_ARRAY,
            $this->getConfigService()->getAllowedFileTypes()
        ));
        $component->addProp(new Prop(
            'max-file-size',
            Prop::TYPE_NUMBER,
            $this->getConfigService()->getMaxAttachmentSize()
        ));
        $this->setComponent($component);
    }

    /**
     * @inheritDoc
     */
    public function isCapable(Request $request): bool
    {
        $id = $request->attributes->getInt('id');
        $claimRequest = $this->getClaimService()->getClaimDao()->getClaimRequestById($id);
        if (
            !$claimRequest instanceof ClaimRequest
            || !$this->getUserRoleManagerHelper()->isEmployeeAccessible($claimRequest->getEmployee()->getEmpNumber())
        ) {
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        return true;
    }
}
