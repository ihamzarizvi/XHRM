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

namespace XHRM\Dashboard\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Dashboard\Dto\ActionSummary\PendingAction;
use XHRM\Dashboard\Dto\ActionSummary\PendingAppraisalReviewSummary;
use XHRM\Dashboard\Dto\ActionSummary\PendingLeaveRequestSummary;
use XHRM\Dashboard\Dto\ActionSummary\PendingSelfReviewSummary;
use XHRM\Dashboard\Dto\ActionSummary\PendingTimesheetSummary;
use XHRM\Dashboard\Dto\ActionSummary\ScheduledInterviewSummary;
use XHRM\Dashboard\Traits\Service\EmployeeActionSummaryServiceTrait;
use XHRM\Dashboard\Traits\Service\ModuleServiceTrait;
use XHRM\Entity\Candidate;
use XHRM\Entity\Employee;

class EmployeeActionSummaryAPI extends Endpoint implements ResourceEndpoint
{
    use UserRoleManagerTrait;
    use AuthUserTrait;
    use EmployeeActionSummaryServiceTrait;
    use ModuleServiceTrait;

    public const LEAVE_MODULE = 'leave';
    public const TIME_MODULE = 'time';
    public const PERFORMANCE_MODULE = 'performance';
    public const RECRUITMENT_MODULE = 'recruitment';

    /**
     * @OA\Get(
     *     path="/api/v2/dashboard/employees/action-summary",
     *     tags={"Dashboard/Widgets"},
     *     summary="Get My Action Summary",
     *     operationId="get-my-action-summary",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="group", type="string", example="Leave Requests To Approve"),
     *             @OA\Property(property="pendingActionCount", type="integer", example="1"),
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $empNumber = $this->getAuthUser()->getEmpNumber();

        $accessibleEmpNumbers = $this->getUserRoleManager()->getAccessibleEntityIds(Employee::class);
        $authUserIndex = array_search($empNumber, $accessibleEmpNumbers);
        if ($authUserIndex !== false) {
            unset($accessibleEmpNumbers[$authUserIndex]);
        }

        $accessibleCandidateIds = $this->getUserRoleManager()->getAccessibleEntityIds(Candidate::class);

        $enabledModuleNames = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();

        $availableActionGroups = [];
        if (in_array(self::LEAVE_MODULE, $enabledModuleNames)) {
            $availableActionGroups[] = new PendingLeaveRequestSummary(array_values($accessibleEmpNumbers));
        }
        if ((in_array(self::TIME_MODULE, $enabledModuleNames))) {
            $availableActionGroups[] = new PendingTimesheetSummary(array_values($accessibleEmpNumbers));
        }
        if (in_array(self::PERFORMANCE_MODULE, $enabledModuleNames)) {
            $availableActionGroups[] = new PendingAppraisalReviewSummary($empNumber);
            $availableActionGroups[] = new PendingSelfReviewSummary($empNumber);
        }
        if (in_array(self::RECRUITMENT_MODULE, $enabledModuleNames)) {
            $availableActionGroups[] = new ScheduledInterviewSummary($accessibleCandidateIds);
        }

        $actionsSummary = [];
        foreach ($availableActionGroups as $actionGroup) {
            $pendingAction = new PendingAction($actionGroup);
            $actionSummary = $pendingAction->generateActionSummary();
            if (!is_null($actionSummary)) {
                $actionsSummary[] = $actionSummary;
            }
        }

        return new EndpointResourceResult(ArrayModel::class, $actionsSummary);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        $paramRules = new ParamRuleCollection();
        $paramRules->addExcludedParamKey(CommonParams::PARAMETER_ID);
        return $paramRules;
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
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

