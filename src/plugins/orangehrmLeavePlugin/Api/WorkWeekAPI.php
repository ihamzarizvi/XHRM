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
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\ResourceEndpoint;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Entity\WorkWeek;
use XHRM\Leave\Api\Model\WorkWeekIndexedModel;
use XHRM\Leave\Api\Model\WorkWeekModel;
use XHRM\Leave\Traits\Service\WorkWeekServiceTrait;

class WorkWeekAPI extends Endpoint implements ResourceEndpoint
{
    use WorkWeekServiceTrait;

    public const PARAMETER_MONDAY = 'monday';
    public const PARAMETER_TUESDAY = 'tuesday';
    public const PARAMETER_WEDNESDAY = 'wednesday';
    public const PARAMETER_THURSDAY = 'thursday';
    public const PARAMETER_FRIDAY = 'friday';
    public const PARAMETER_SATURDAY = 'saturday';
    public const PARAMETER_SUNDAY = 'sunday';

    public const FILTER_MODEL = 'model';

    public const MODEL_DEFAULT = 'default';
    public const MODEL_INDEXED = 'indexed';
    public const MODEL_MAP = [
        self::MODEL_DEFAULT => WorkWeekModel::class,
        self::MODEL_INDEXED => WorkWeekIndexedModel::class,
    ];

    public const PARAMETER_WORKWEEK = [
        self::PARAMETER_MONDAY,
        self::PARAMETER_TUESDAY,
        self::PARAMETER_WEDNESDAY,
        self::PARAMETER_THURSDAY,
        self::PARAMETER_FRIDAY,
        self::PARAMETER_SATURDAY,
        self::PARAMETER_SUNDAY,
    ];

    /**
     * @OA\Get(
     *     path="/api/v2/leave/workweek",
     *     tags={"Leave/Work Week"},
     *     summary="Get Work Week",
     *     operationId="get-work-week",
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={XHRM\Leave\Api\WorkWeekAPI::MODEL_DEFAULT, XHRM\Leave\Api\WorkWeekAPI::MODEL_INDEXED},
     *             default=XHRM\Leave\Api\WorkWeekAPI::MODEL_DEFAULT
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(ref="#/components/schemas/Leave-WorkWeekIndexedModel"),
     *                     @OA\Schema(ref="#/components/schemas/Leave-WorkWeekModel"),
     *                 }
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $workWeek = $this->getWorkWeekService()
            ->getWorkWeekDao()
            ->getWorkWeekById($this->getIdFromUrlAttributes());
        $this->throwRecordNotFoundExceptionIfNotExist($workWeek, WorkWeek::class);

        return new EndpointResourceResult($this->getModelClass(), $workWeek);
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
     * @return ParamRule
     */
    protected function getModelParamRule(): ParamRule
    {
        return $this->getValidationDecorator()->notRequiredParamRule(
            new ParamRule(
                self::FILTER_MODEL,
                new Rule(Rules::IN, [array_keys(self::MODEL_MAP)])
            )
        );
    }

    /**
     * @return int
     */
    private function getIdFromUrlAttributes(): int
    {
        return $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
    }

    /**
     * @return ParamRule
     */
    private function getIdParamRule(): ParamRule
    {
        return new ParamRule(CommonParams::PARAMETER_ID, new Rule(Rules::POSITIVE));
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection($this->getIdParamRule(), $this->getModelParamRule());
    }

    /**
     * @OA\Put(
     *     path="/api/v2/leave/workweek",
     *     tags={"Leave/Work Week"},
     *     summary="Update Work Week",
     *     operationId="update-work-week",
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={XHRM\Leave\Api\WorkWeekAPI::MODEL_DEFAULT, XHRM\Leave\Api\WorkWeekAPI::MODEL_INDEXED},
     *             default=XHRM\Leave\Api\WorkWeekAPI::MODEL_DEFAULT
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="monday", type="integer"),
     *             @OA\Property(property="tuesday", type="integer"),
     *             @OA\Property(property="wednesday", type="integer"),
     *             @OA\Property(property="thursday", type="integer"),
     *             @OA\Property(property="friday", type="integer"),
     *             @OA\Property(property="saturday", type="integer"),
     *             @OA\Property(property="sunday", type="integer"),
     *             required={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"},
     *             description="0 - working day, 4 - half day, 8 - non working day"
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(ref="#/components/schemas/Leave-WorkWeekIndexedModel"),
     *                     @OA\Schema(ref="#/components/schemas/Leave-WorkWeekModel"),
     *                 }
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $workWeek = $this->getWorkWeekService()
            ->getWorkWeekDao()
            ->getWorkWeekById($this->getIdFromUrlAttributes());
        $this->throwRecordNotFoundExceptionIfNotExist($workWeek, WorkWeek::class);
        foreach (self::PARAMETER_WORKWEEK as $workWeekKey) {
            $setter = 'set' . ucfirst($workWeekKey);
            $workWeek->$setter($this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, $workWeekKey));
        }

        $workWeek = $this->getWorkWeekService()
            ->getWorkWeekDao()
            ->saveWorkWeek($workWeek);

        return new EndpointResourceResult($this->getModelClass(), $workWeek);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        $paramRules = new ParamRuleCollection($this->getIdParamRule(), $this->getModelParamRule());
        foreach (self::PARAMETER_WORKWEEK as $workWeekKey) {
            $paramRules->addParamValidation(
                new ParamRule($workWeekKey, new Rule(Rules::IN, [WorkWeek::WORKWEEK_LENGTHS, true]))
            );
        }
        return $paramRules;
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
