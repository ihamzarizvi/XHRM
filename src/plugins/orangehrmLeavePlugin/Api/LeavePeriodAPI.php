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

namespace XHRM\Leave\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CrudEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Service\MenuService;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Entity\LeavePeriodHistory;
use XHRM\Framework\Services;
use XHRM\Leave\Api\Model\LeavePeriodHistoryModel;
use XHRM\Leave\Api\Model\LeavePeriodModel;
use XHRM\Leave\Traits\Service\LeaveConfigServiceTrait;
use XHRM\Leave\Traits\Service\LeavePeriodServiceTrait;

class LeavePeriodAPI extends Endpoint implements CrudEndpoint
{
    use LeavePeriodServiceTrait;
    use LeaveConfigServiceTrait;
    use NormalizerServiceTrait;
    use DateTimeHelperTrait;

    public const PARAMETER_START_MONTH = 'startMonth';
    public const PARAMETER_START_DAY = 'startDay';

    public const META_PARAMETER_LEAVE_PERIOD_DEFINED = 'leavePeriodDefined';
    public const META_PARAMETER_CURRENT_LEAVE_PERIOD = 'currentLeavePeriod';

    /**
     * @OA\Get(
     *     path="/api/v2/leave/leave-period",
     *     tags={"Leave/Leave Period"},
     *     summary="Get Current Leave Period",
     *     operationId="get-current-leave-period",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Leave-LeavePeriodHistoryModel"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentLeavePeriod",
     *                     ref="#/components/schemas/Leave-LeavePeriodModel"
     *                 ),
     *                 @OA\Property(property="leavePeriodDefined", type="boolean"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $leavePeriodHistory = $this->getLeavePeriodService()->getCurrentLeavePeriodStartDateAndMonth();
        $leavePeriodDefined = $this->getLeaveConfigService()->isLeavePeriodDefined();
        if (!$leavePeriodDefined) {
            $leavePeriodHistory = new LeavePeriodHistory();
            $leavePeriodHistory->setStartMonth(1);
            $leavePeriodHistory->setStartDay(1);
            $leavePeriodHistory->setCreatedAt($this->getDateTimeHelper()->getNow());
        }
        return new EndpointResourceResult(
            LeavePeriodHistoryModel::class,
            $leavePeriodHistory,
            new ParameterBag(
                [
                    self::META_PARAMETER_LEAVE_PERIOD_DEFINED => $leavePeriodDefined,
                    self::META_PARAMETER_CURRENT_LEAVE_PERIOD => $this->getCurrentLeavePeriod($leavePeriodDefined),
                ]
            )
        );
    }

    /**
     * @param bool $leavePeriodDefined
     * @return array|null
     */
    private function getCurrentLeavePeriod(bool $leavePeriodDefined): ?array
    {
        return $leavePeriodDefined ?
            $this->getNormalizerService()->normalize(
                LeavePeriodModel::class,
                $this->getLeavePeriodService()->getCurrentLeavePeriod(true)
            ) : null;
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
     * @OA\Get(
     *     path="/api/v2/leave/leave-periods",
     *     tags={"Leave/Leave Period"},
     *     summary="List All Leave Periods",
     *     operationId="list-all-leave-periods",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Leave-LeavePeriodModel"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentLeavePeriod",
     *                     ref="#/components/schemas/Leave-LeavePeriodModel"
     *                 ),
     *                 @OA\Property(property="leavePeriodDefined", type="boolean"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $leavePeriodDefined = $this->getLeaveConfigService()->isLeavePeriodDefined();
        return new EndpointCollectionResult(
            LeavePeriodModel::class,
            $this->getLeavePeriodService()->getGeneratedLeavePeriodList(),
            new ParameterBag(
                [
                    self::META_PARAMETER_LEAVE_PERIOD_DEFINED => $leavePeriodDefined,
                    self::META_PARAMETER_CURRENT_LEAVE_PERIOD => $this->getCurrentLeavePeriod($leavePeriodDefined),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    /**
     * @OA\Put(
     *     path="/api/v2/leave/leave-period",
     *     tags={"Leave/Leave Period"},
     *     summary="Update Leave Period",
     *     operationId="update-leave-period",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="startDay", type="integer"),
     *             @OA\Property(property="startMonth", type="integer")
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Leave-LeavePeriodHistoryModel"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentLeavePeriod",
     *                     ref="#/components/schemas/Leave-LeavePeriodModel"
     *                 ),
     *                 @OA\Property(property="leavePeriodDefined", type="boolean"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $leavePeriodDefined = $this->getLeaveConfigService()->isLeavePeriodDefined();
        $leavePeriodHistory = new LeavePeriodHistory();
        $leavePeriodHistory->setStartMonth(
            $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_START_MONTH)
        );
        $leavePeriodHistory->setStartDay(
            $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_START_DAY)
        );
        $leavePeriodHistory->setCreatedAt($this->getDateTimeHelper()->getNow());
        $this->getLeavePeriodService()
            ->getLeavePeriodDao()
            ->saveLeavePeriodHistory($leavePeriodHistory);

        if (!$leavePeriodDefined) {
            /** @var MenuService $menuService */
            $menuService = $this->getContainer()->get(Services::MENU_SERVICE);
            $menuService->enableModuleMenuItems('leave');
        }
        return new EndpointResourceResult(
            LeavePeriodHistoryModel::class,
            $leavePeriodHistory,
            new ParameterBag(
                [
                    self::META_PARAMETER_LEAVE_PERIOD_DEFINED => $leavePeriodDefined,
                    self::META_PARAMETER_CURRENT_LEAVE_PERIOD => $this->getCurrentLeavePeriod($leavePeriodDefined),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        $paramRules = new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_START_MONTH,
                new Rule(Rules::IN, [$this->getLeavePeriodService()->getMonthNumberList()])
            ),
            new ParamRule(
                self::PARAMETER_START_DAY,
                new Rule(Rules::POSITIVE),
                new Rule(Rules::CALLBACK, [
                    function (int $startDay) {
                        $startMonth = $this->getRequestParams()->getInt(
                            RequestParams::PARAM_TYPE_BODY,
                            self::PARAMETER_START_MONTH
                        );
                        $allowedDaysForMonth = $this->getLeavePeriodService()->getListOfDates($startMonth, false);
                        return in_array($startDay, $allowedDaysForMonth);
                    }
                ])
            ),
        );
        $paramRules->addExcludedParamKey(CommonParams::PARAMETER_ID);
        return $paramRules;
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
