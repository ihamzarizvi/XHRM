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

namespace XHRM\Leave\Api;

use Exception;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Exception\BadRequestException;
use XHRM\Core\Api\V2\Exception\ForbiddenException;
use XHRM\Core\Api\V2\Exception\RecordNotFoundException;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Leave\Api\Model\LeaveRequestModel;
use XHRM\Leave\Api\Traits\LeaveRequestParamHelperTrait;
use XHRM\Leave\Api\Traits\LeaveRequestPermissionTrait;
use XHRM\Leave\Traits\Service\LeaveRequestServiceTrait;
use XHRM\ORM\Exception\TransactionException;

class EmployeeBulkLeaveRequestAPI extends Endpoint implements ResourceEndpoint
{
    use LeaveRequestParamHelperTrait;
    use LeaveRequestServiceTrait;
    use UserRoleManagerTrait;
    use AuthUserTrait;
    use LeaveRequestPermissionTrait;
    use EntityManagerHelperTrait;

    public const PARAMETER_ACTION = 'action';
    public const PARAMETER_LEAVE_REQUEST_ID = 'leaveRequestId';
    public const PARAMETER_DATA = 'data';

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
     * @OA\Put(
     *     path="/api/v2/leave/employees/leave-requests/bulk",
     *     tags={"Leave/Employee Bulk Leave Request"},
     *     summary="Bulk Approve/Cancel/Reject Leave Requests",
     *     operationId="bulk-approve-cancel-reject-leave-requests",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="leaveRequestId", type="integer"),
     *             @OA\Property(property="action", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Leave-LeaveRequestModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound"),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", default="400"),
     *                 @OA\Property(property="message", type="string", example="Leave request has multiple statuses, Performed action not allowed")
     *             )
     *         )
     *     ),
     * )
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $leaveRequestsIdActionMap = $this->getLeaveRequestsIdActionMap();

        $this->beginTransaction();
        try {
            $leaveRequests = $this->getLeaveRequestService()
                ->getLeaveRequestDao()
                ->getLeaveRequestsByLeaveRequestIds(array_keys($leaveRequestsIdActionMap));

            if (count($leaveRequestsIdActionMap) !== count($leaveRequests)) {
                throw $this->getRecordNotFoundException();
            }
            foreach ($leaveRequests as $leaveRequest) {
                $this->checkLeaveRequestAccessible($leaveRequest);
            }

            $detailedLeaveRequests = $this->getLeaveRequestService()
                ->getDetailedLeaveRequests($leaveRequests);

            foreach ($detailedLeaveRequests as $detailedLeaveRequest) {
                if ($detailedLeaveRequest->hasMultipleStatus()) {
                    throw $this->getBadRequestException('Leave request have multiple status');
                }

                $action = $leaveRequestsIdActionMap[$detailedLeaveRequest->getLeaveRequest()->getId()];
                if (!$detailedLeaveRequest->isActionAllowed($action)) {
                    throw $this->getBadRequestException('Performed action not allowed');
                }

                $workflow = $detailedLeaveRequest->getWorkflowForAction($action);
                $this->getLeaveRequestService()->changeLeaveRequestStatus($detailedLeaveRequest, $workflow);
            }

            $this->commitTransaction();

            return new EndpointCollectionResult(LeaveRequestModel::class, $leaveRequests);
        } catch (RecordNotFoundException | ForbiddenException | BadRequestException $e) {
            $this->rollBackTransaction();
            throw $e;
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }
    }

    /**
     * @return array<int, string> e.g. array(leaveRequestId => action)
     * @throws BadRequestException
     */
    private function getLeaveRequestsIdActionMap(): array
    {
        $leaveRequestsData = $this->getRequestParams()->getArray(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_DATA
        );
        $leaveRequestsIdActionMap = [];
        foreach ($leaveRequestsData as $leaveRequestData) {
            if (isset($leaveRequestsIdActionMap[$leaveRequestData[self::PARAMETER_LEAVE_REQUEST_ID]])) {
                throw $this->getBadRequestException(
                    'Multiple actions defined for the leave request id: ' .
                    $leaveRequestData[self::PARAMETER_LEAVE_REQUEST_ID]
                );
            }
            $leaveRequestsIdActionMap[$leaveRequestData[self::PARAMETER_LEAVE_REQUEST_ID]] = $leaveRequestData[self::PARAMETER_ACTION];
        }
        return $leaveRequestsIdActionMap;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATA,
                new Rule(Rules::ARRAY_TYPE),
                new Rule(
                    Rules::EACH,
                    [
                        new Rules\Composite\AllOf(
                            new Rule(
                                Rules::KEY,
                                [
                                    self::PARAMETER_LEAVE_REQUEST_ID,
                                    new Rules\Composite\AllOf(new Rule(Rules::POSITIVE))
                                ]
                            ),
                            new Rule(
                                Rules::KEY,
                                [self::PARAMETER_ACTION, new Rules\Composite\AllOf(new Rule(Rules::STRING_TYPE))]
                            ),
                        )
                    ]
                )
            ),
        );
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

