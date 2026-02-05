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

namespace XHRM\Pim\Api;

use OpenApi\Annotations as OA;
use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Entity\Employee;
use XHRM\Pim\Api\Model\EmployeeModel;
use XHRM\Pim\Api\Model\MyInfoDetailedModel;
use XHRM\Pim\Api\Model\EmployeeDetailedModel;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;

class MyInfoAPI extends Endpoint implements ResourceEndpoint
{
    use AuthUserTrait;
    use EmployeeServiceTrait;

    public const FILTER_MODEL = 'model';
    public const MODEL_DEFAULT = 'default';
    public const MODEL_DETAILED = 'detailed';
    public const MODEL_SUMMARY = 'summary';
    public const MODEL_MAP = [
        self::MODEL_DEFAULT => EmployeeModel::class,
        self::MODEL_SUMMARY => MyInfoDetailedModel::class,
        self::MODEL_DETAILED => EmployeeDetailedModel::class
    ];

    /**
     * @OA\Get(
     *     path="/api/v2/pim/myself",
     *     tags={"PIM/Employee"},
     *     summary="Get My Details",
     *     operationId="get-my-details",
     *     description="This endpoint allows you to get employee details for the currently logged in employee. In other words, get the details of the employee making this request.",
     *     @OA\Parameter(
     *         name="model",
     *         description="Specify whether the model should be default, my info or detailed.",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={XHRM\Pim\Api\MyInfoAPI::MODEL_DEFAULT, XHRM\Pim\Api\MyInfoAPI::MODEL_DETAILED, XHRM\Pim\Api\MyInfoAPI::MODEL_SUMMARY},
     *             default=XHRM\Pim\Api\MyInfoAPI::MODEL_DEFAULT
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(ref="#/components/schemas/Pim-EmployeeModel"),
     *                     @OA\Schema(ref="#/components/schemas/Pim-MyInfoDetailedModel"),
     *                     @OA\Schema(ref="#/components/schemas/Pim-EmployeeDetailedModel"),
     *                 }
     *             ),
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $empNumber = $this->getAuthUser()->getEmpNumber();
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        return new EndpointResourceResult(
            $this->getModelClass(),
            $employee,
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        $paramRules = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_MODEL,
                    new Rule(Rules::IN, [array_keys(self::MODEL_MAP)])
                )
            ),
        );
        $paramRules->addExcludedParamKey(CommonParams::PARAMETER_ID);
        return $paramRules;
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        $model = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_MODEL,
            self::MODEL_DEFAULT
        );
        return self::MODEL_MAP[$model];
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
