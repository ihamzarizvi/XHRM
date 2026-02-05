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

namespace XHRM\Admin\Api;

use XHRM\Admin\Api\Model\CurrencyTypeModel;
use XHRM\Admin\Dto\AllowedPayGradeCurrencySearchFilterParams;
use XHRM\Admin\Dto\PayGradeCurrencySearchFilterParams;
use XHRM\Admin\Traits\Service\PayGradeServiceTrait;
use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CollectionEndpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;

class PayGradeAllowedCurrencyAPI extends Endpoint implements CollectionEndpoint
{
    use PayGradeServiceTrait;

    /**
     * @OA\Get(
     *     path="/api/v2/admin/pay-grades/{payGradeId}/currencies/allowed",
     *     tags={"Admin/Pay Grade Currency"},
     *     summary="List Allowed Currencies for Pay Grade",
     *     operationId="list-allowed-currencies-for-pay-grade",
     *     @OA\PathParameter(
     *         name="payGradeId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=PayGradeCurrencySearchFilterParams::ALLOWED_SORT_FIELDS)
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
     *                 @OA\Items(ref="#/components/schemas/Admin-CurrencyTypeModel")
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $payGradeId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, PayGradeCurrencySearchFilterParams::PARAMETER_PAY_GRADE_ID);
        $payGradeCurrencySearchFilterParams = new AllowedPayGradeCurrencySearchFilterParams();
        $this->setSortingAndPaginationParams($payGradeCurrencySearchFilterParams);
        $payGradeCurrencySearchFilterParams->setPayGradeId($payGradeId);
        $allowedCurrencies = $this->getPayGradeService()->getAllowedPayCurrencies($payGradeCurrencySearchFilterParams);
        $count = $this->getPayGradeService()->getAllowedPayCurrenciesCount($payGradeCurrencySearchFilterParams);
        return new EndpointCollectionResult(
            CurrencyTypeModel::class,
            $allowedCurrencies,
            new ParameterBag([
                PayGradeCurrencySearchFilterParams::PARAMETER_PAY_GRADE_ID => $payGradeId,
                CommonParams::PARAMETER_TOTAL => $count,
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
                PayGradeCurrencySearchFilterParams::PARAMETER_PAY_GRADE_ID,
                new Rule(Rules::POSITIVE)
            ),
            ...$this->getSortingAndPaginationParamsRules(AllowedPayGradeCurrencySearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    public function create(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}

