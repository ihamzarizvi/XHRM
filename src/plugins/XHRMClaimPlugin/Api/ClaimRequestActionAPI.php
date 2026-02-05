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

namespace XHRM\Claim\Api;

use Exception;
use OpenApi\Annotations as OA;
use XHRM\Claim\Api\Model\ClaimRequestModel;
use XHRM\Claim\Api\Traits\ClaimRequestAPIHelperTrait;
use XHRM\Claim\Traits\Service\ClaimServiceTrait;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Exception\ForbiddenException;
use XHRM\Core\Api\V2\Exception\InvalidParamException;
use XHRM\Core\Api\V2\Exception\RecordNotFoundException;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\ClaimRequest;
use XHRM\Entity\Employee;
use XHRM\Entity\WorkflowStateMachine;
use XHRM\ORM\Exception\TransactionException;

class ClaimRequestActionAPI extends Endpoint implements ResourceEndpoint
{
    use AuthUserTrait;
    use ClaimRequestAPIHelperTrait;
    use EntityManagerHelperTrait;
    use ClaimServiceTrait;
    use DateTimeHelperTrait;

    public const PARAMETER_REQUEST_ID = 'requestId';
    public const PARAMETER_ACTION = 'action';
    public const ACTIONABLE_STATES_MAP = [
        WorkflowStateMachine::CLAIM_ACTION_SUBMIT => 'SUBMIT',
        WorkflowStateMachine::CLAIM_ACTION_APPROVE => 'APPROVE',
        WorkflowStateMachine::CLAIM_ACTION_PAY => 'PAY',
        WorkflowStateMachine::CLAIM_ACTION_CANCEL => 'CANCEL',
        WorkflowStateMachine::CLAIM_ACTION_REJECT => 'REJECT'
    ];

    /**
     * @OA\Put(
     *     path="/api/v2/claim/requests/{requestId}/action",
     *     tags={"Claim/Requests"},
     *     summary="Perform an Action on a Claim Request",
     *     operationId="perform-an-action-on-a-claim-request",
     *     @OA\PathParameter(
     *         name="requestId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="action", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Claim-RequestModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $this->beginTransaction();
        try {
            $requestId = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                self::PARAMETER_REQUEST_ID
            );
            $action = $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_ACTION
            );

            $claimRequest = $this->getClaimRequest($requestId);

            $actionIndex = array_flip(self::ACTIONABLE_STATES_MAP)[$action];

            $this->isActionAllowed($actionIndex, $claimRequest);

            $claimRequest->setStatus($this->getResultingState($claimRequest, $actionIndex));

            if ($actionIndex == WorkflowStateMachine::CLAIM_ACTION_SUBMIT) {
                $claimRequest->setSubmittedDate($this->getDateTimeHelper()->getNow());
            }

            $this->getClaimService()->getClaimDao()->saveClaimRequest($claimRequest);
            $this->commitTransaction();
        } catch (ForbiddenException | InvalidParamException | RecordNotFoundException $e) {
            $this->rollBackTransaction();
            throw $e;
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }

        return new EndpointResourceResult(
            ClaimRequestModel::class,
            $claimRequest
        );
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_REQUEST_ID,
                new Rule(Rules::POSITIVE)
            ),
            new ParamRule(
                self::PARAMETER_ACTION,
                new Rule(Rules::IN, [self::ACTIONABLE_STATES_MAP])
            )
        );
    }

    /**
     * @param ClaimRequest $claimRequest
     * @param String $actionIndex
     * @return string
     * @throws InvalidParamException
     */
    private function getResultingState(ClaimRequest $claimRequest, string $actionIndex): string
    {
        $workflowItems = $this->getUserRoleManager()->getAllowedActions(
            WorkflowStateMachine::FLOW_CLAIM,
            $claimRequest->getStatus(),
            [],
            [],
            [Employee::class => $claimRequest->getEmployee()->getEmpNumber()]
        );

        foreach ($workflowItems as $workflowItem) {
            if ($workflowItem->getAction() == $actionIndex) {
                return $workflowItem->getResultingState();
            }
        }
        throw $this->getInvalidParamException(self::PARAMETER_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}

