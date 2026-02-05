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

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Entity\LeaveEntitlement;
use XHRM\Leave\Api\Traits\LeaveEntitlementPermissionTrait;
use XHRM\Leave\Traits\Service\LeaveEntitlementServiceTrait;

class ValidationLeaveEntitlementValueAPI extends Endpoint implements ResourceEndpoint
{
    use LeaveEntitlementServiceTrait;
    use LeaveEntitlementPermissionTrait;

    public const PARAMETER_ENTITLEMENT = 'entitlement';

    public const PARAMETER_VALID = 'valid';
    public const PARAMETER_DAYS_USED = 'daysUsed';

    /**
     * @OA\Get(
     *     path="/api/v2/leave/leave-entitlements/{id}/validation/entitlements",
     *     tags={"Leave/Validation"},
     *     summary="Validate Leave Entitlement",
     *     operationId="validate-leave-entitlement",
     *     @OA\PathParameter(
     *         name="id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="entitlement",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="valid", type="boolean"),
     *                     @OA\Property(property="daysUsed", type="integer"),
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         ),
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
        $leaveEntitlement = $this->getLeaveEntitlementService()->getLeaveEntitlementDao()->getLeaveEntitlement($id);
        $this->throwRecordNotFoundExceptionIfNotExist($leaveEntitlement, LeaveEntitlement::class);
        $this->checkLeaveEntitlementAccessible($leaveEntitlement);

        $entitlement = $this->getRequestParams()->getFloat(
            RequestParams::PARAM_TYPE_QUERY,
            self::PARAMETER_ENTITLEMENT
        );

        $isValidEntitlement = true;
        if ($leaveEntitlement->getDaysUsed() > $entitlement) {
            $isValidEntitlement = false;
        }
        return new EndpointResourceResult(
            ArrayModel::class,
            [
                self::PARAMETER_VALID => $isValidEntitlement,
                self::PARAMETER_DAYS_USED => $leaveEntitlement->getDaysUsed(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            new ParamRule(self::PARAMETER_ENTITLEMENT, new Rule(Rules::ZERO_OR_POSITIVE))
        );
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

