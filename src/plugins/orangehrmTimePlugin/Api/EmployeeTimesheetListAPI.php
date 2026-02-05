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

namespace XHRM\Time\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CollectionEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\WorkflowStateMachine;
use XHRM\Time\Api\Model\EmployeeTimesheetModel;
use XHRM\Time\Dto\EmployeeTimesheetListSearchFilterParams;
use XHRM\Time\Traits\Service\TimesheetServiceTrait;

class EmployeeTimesheetListAPI extends Endpoint implements CollectionEndpoint
{
    use TimesheetServiceTrait;
    use UserRoleManagerTrait;

    public const FILTER_EMP_NUMBER = 'empNumber';

    /**
     * @OA\Get(
     *     path="/api/v2/time/employees/timesheets/list",
     *     tags={"Time/Employee Timesheet"},
     *     summary="List All Employee Timesheets",
     *     operationId="list-all-employee-timesheets",
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=EmployeeTimesheetListSearchFilterParams::ALLOWED_SORT_FIELDS)
     *     ),
     *     @OA\Parameter(
     *         name="empNumber",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/sortOrder"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(ref="#/components/parameters/offset"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Time-EmployeeTimesheetModel",
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $employeeTimesheetListSearchParamHolder = new EmployeeTimesheetListSearchFilterParams();
        $this->setSortingAndPaginationParams($employeeTimesheetListSearchParamHolder);
        $empNumber = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_EMP_NUMBER
        );

        if (!is_null($empNumber)) {
            $employeeTimesheetListSearchParamHolder->setEmployeeNumbers([$empNumber]);
        } else {
            $accessibleEmpNumbers = $this->getUserRoleManager()->getAccessibleEntityIds(Employee::class);
            $employeeTimesheetListSearchParamHolder->setEmployeeNumbers($accessibleEmpNumbers);
        }

        $actions = [WorkflowStateMachine::TIMESHEET_ACTION_APPROVE, WorkflowStateMachine::TIMESHEET_ACTION_REJECT];
        $actionableStatesList = $this->getUserRoleManager()
            ->getActionableStates(WorkflowStateMachine::FLOW_TIME_TIMESHEET, $actions);
        $employeeTimesheetListSearchParamHolder->setActionableStatesList($actionableStatesList);

        $employeeTimesheetList = $this->getTimesheetService()
            ->getTimesheetDao()
            ->getEmployeeTimesheetList($employeeTimesheetListSearchParamHolder);

        $employeeTimesheetListCount = $this->getTimesheetService()
            ->getTimesheetDao()
            ->getEmployeeTimesheetListCount($employeeTimesheetListSearchParamHolder);

        return new EndpointCollectionResult(
            EmployeeTimesheetModel::class,
            $employeeTimesheetList,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => $employeeTimesheetListCount])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    CommonParams::PARAMETER_EMP_NUMBER,
                    new Rule(Rules::POSITIVE),
                    new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
                )
            ),
            ...$this->getSortingAndPaginationParamsRules(EmployeeTimesheetListSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
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
