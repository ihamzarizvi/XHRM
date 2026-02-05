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

namespace XHRM\Maintenance\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CollectionEndpoint;
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
use XHRM\Entity\Vacancy;
use XHRM\Maintenance\Api\Model\PurgeCandidateListModel;
use XHRM\Maintenance\Api\Model\PurgeCandidateModel;
use XHRM\Maintenance\Service\PurgeService;
use XHRM\ORM\Exception\TransactionException;
use XHRM\Recruitment\Dto\CandidateSearchFilterParams;
use XHRM\Recruitment\Traits\Service\CandidateServiceTrait;

class PurgeCandidateAPI extends Endpoint implements CollectionEndpoint
{
    use CandidateServiceTrait;

    private ?PurgeService $purgeService = null;

    public const PARAMETER_VACANCY_ID = 'vacancyId';

    /**
     * @return PurgeService
     */
    public function getPurgeService(): PurgeService
    {
        if (is_null($this->purgeService)) {
            $this->purgeService = new PurgeService();
        }
        return $this->purgeService;
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/maintenance/candidates/purge",
     *     tags={"Maintenance/Purge Candidate"},
     *     summary="Purge Candidate",
     *     operationId="purge-candidate",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="vacancyId", type="integer"),
     *             required={"vacancyId"}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Maintenance-PurgeCandidateModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     * @throws TransactionException
     */
    public function delete(): EndpointResult
    {
        $vacancyId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_VACANCY_ID
        );
        $this->getPurgeService()->purgeCandidateData($vacancyId);
        return new EndpointResourceResult(PurgeCandidateModel::class, $vacancyId);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_VACANCY_ID,
                new Rule(Rules::POSITIVE),
                new Rule(Rules::ENTITY_ID_EXISTS, [Vacancy::class])
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/maintenance/candidates",
     *     tags={"Maintenance/Purge Candidate"},
     *     summary="List Purgeable Candidates for a Vacancy",
     *     operationId="list-purgeable-candidates-for-a-vacancy",
     *     @OA\Parameter(
     *         name="vacancyId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=CandidateSearchFilterParams::ALLOWED_SORT_FIELDS)
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
     *                 ref="#/components/schemas/Maintenance-PurgeCandidateListModel"
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
        $candidateSearchFilterParamHolder = new CandidateSearchFilterParams();

        $candidateSearchFilterParamHolder->setVacancyId(
            $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_QUERY,
                self::PARAMETER_VACANCY_ID
            )
        );
        $candidateSearchFilterParamHolder->setConsentToKeepData(false);

        $this->setSortingAndPaginationParams($candidateSearchFilterParamHolder);

        $candidates = $this->getCandidateService()->getCandidateDao()->getCandidatesList($candidateSearchFilterParamHolder);
        $count = $this->getCandidateService()->getCandidateDao()->getCandidatesCount($candidateSearchFilterParamHolder);

        return new EndpointCollectionResult(
            PurgeCandidateListModel::class,
            $candidates,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => $count])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_VACANCY_ID,
                new Rule(Rules::POSITIVE),
                new Rule(Rules::ENTITY_ID_EXISTS, [Vacancy::class])
            ),
            ...$this->getSortingAndPaginationParamsRules([])
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
}

