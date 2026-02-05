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
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Dashboard\Api\Model\EmployeesOnLeaveListModel;
use XHRM\Entity\Employee;
use XHRM\Leave\Traits\Service\LeaveConfigServiceTrait;
use XHRM\Dashboard\Dto\EmployeeOnLeaveSearchFilterParams;
use XHRM\Dashboard\Traits\Service\EmployeeOnLeaveServiceTrait;

class EmployeeOnLeaveAPI extends Endpoint implements CollectionEndpoint
{
    use DateTimeHelperTrait;
    use LeaveConfigServiceTrait;
    use EmployeeOnLeaveServiceTrait;
    use ConfigServiceTrait;
    use UserRoleManagerTrait;
    use AuthUserTrait;

    public const PARAMETER_DATE = 'date';
    public const META_PARAMETER_LEAVE_PERIOD_DEFINED = 'leavePeriodDefined';

    /**
     * @OA\Get(
     *     path="/api/v2/dashboard/employees/leaves",
     *     tags={"Dashboard/Widgets"},
     *     summary="Get Employees on Leave Today",
     *     operationId="get-employees-on-leave-today",
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=EmployeeOnLeaveSearchFilterParams::ALLOWED_SORT_FIELDS)
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
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Dashboard-EmployeeOnLeaveListModel")
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="leavePeriodDefined", type="boolean"),
     *             )
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $employeeOnLeaveSearchFilterParams = new EmployeeOnLeaveSearchFilterParams();

        $this->setSortingAndPaginationParams($employeeOnLeaveSearchFilterParams);
        $date = $this->getRequestParams()->getDateTime(
            RequestParams::PARAM_TYPE_QUERY,
            self::PARAMETER_DATE,
            null,
            $this->getDateTimeHelper()->getNow()
        );

        $employeeOnLeaveSearchFilterParams->setDate($date);

        $showOnlyAccessibleEmployeesOnLeaveToday = $this->getConfigService()
            ->getDashboardEmployeesOnLeaveTodayShowOnlyAccessibleConfig();

        if ($showOnlyAccessibleEmployeesOnLeaveToday) {
            $accessibleEmpNumbers = $this->getUserRoleManager()->getAccessibleEntityIds(Employee::class);
            $employeeOnLeaveSearchFilterParams->setAccessibleEmpNumber([$this->getAuthUser()->getEmpNumber(), ...$accessibleEmpNumbers]);
        }

        $leavePeriodDefined = $this->getLeaveConfigService()->isLeavePeriodDefined();

        $empLeaveList = $this->getEmployeeOnLeaveService()->getEmployeeOnLeaveDao()
            ->getEmployeeOnLeaveList($employeeOnLeaveSearchFilterParams);
        $employeeCount = $this->getEmployeeOnLeaveService()->getEmployeeOnLeaveDao()
            ->getEmployeeOnLeaveCount($employeeOnLeaveSearchFilterParams);

        return new EndpointCollectionResult(
            EmployeesOnLeaveListModel::class,
            [$empLeaveList],
            new ParameterBag([
                CommonParams::PARAMETER_TOTAL => $employeeCount,
                self::META_PARAMETER_LEAVE_PERIOD_DEFINED => $leavePeriodDefined,
            ])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_DATE,
                new Rule(Rules::API_DATE)
            ),
            ...$this->getSortingAndPaginationParamsRules(EmployeeOnLeaveSearchFilterParams::ALLOWED_SORT_FIELDS),
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

