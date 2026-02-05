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

namespace XHRM\Claim\Api\Traits;

use XHRM\Claim\Traits\Service\ClaimServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\ClaimRequest;
use XHRM\Entity\Employee;
use XHRM\Entity\WorkflowStateMachine;
use XHRM\Core\Api\V2\Exception\EndpointExceptionTrait;

trait ClaimRequestAPIHelperTrait
{
    use UserRoleManagerTrait;
    use EndpointExceptionTrait;
    use ClaimServiceTrait;

    /**
     * @param int $action
     * @param ClaimRequest $claimRequest
     * @return bool
     */
    public function isActionAllowed(int $action, ClaimRequest $claimRequest): bool
    {
        $isActionAllowed = $this->getUserRoleManager()->isActionAllowed(
            WorkflowStateMachine::FLOW_CLAIM,
            $claimRequest->getStatus(),
            $action,
            [],
            [],
            [Employee::class => $claimRequest->getEmployee()->getEmpNumber()]
        );
        if (!$isActionAllowed) {
            throw $this->getForbiddenException();
        }
        return true;
    }

    /**
     * @param int $requestId
     * @return ClaimRequest
     */
    public function getClaimRequest(int $requestId): ClaimRequest
    {
        $claimRequest = $this->getClaimService()->getClaimDao()
            ->getClaimRequestById($requestId);
        $this->throwRecordNotFoundExceptionIfNotExist($claimRequest, ClaimRequest::class);
        if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($claimRequest->getEmployee()->getEmpNumber())) {
            throw $this->getForbiddenException();
        }
        return $claimRequest;
    }
}

