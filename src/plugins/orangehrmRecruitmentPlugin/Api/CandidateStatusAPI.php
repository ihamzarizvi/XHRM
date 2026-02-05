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

namespace XHRM\Recruitment\Api;

use XHRM\Core\Api\V2\CollectionEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\I18N\Traits\Service\I18NHelperTrait;
use XHRM\Recruitment\Service\CandidateService;
use XHRM\Recruitment\Traits\Service\VacancyServiceTrait;

class CandidateStatusAPI extends Endpoint implements CollectionEndpoint
{
    use VacancyServiceTrait;
    use I18NHelperTrait;
    /**
     * @OA\Get(
     *     path="/api/v2/recruitment/candidates/status",
     *     tags={"Recruitment/Candidates"},
     *     summary="List All Statuses",
     *     operationId="list-all-statuses",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="label", type="string"),
     *             ),
     *         )
     *     ),
     * )
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $candidateStatus = array_map(function ($key, $value) {
            return [
                'id' => $key,
                'label' => $this->getI18NHelper()->transBySource(ucwords(strtolower($value))),
            ];
        }, array_keys(CandidateService::STATUS_MAP), CandidateService::STATUS_MAP);
        return new EndpointResourceResult(ArrayModel::class, $candidateStatus);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
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
