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

use Exception;
use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CollectionEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\Exception\ForbiddenException;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\Leave;
use XHRM\Entity\LeaveComment;
use XHRM\Leave\Api\Model\LeaveCommentModel;
use XHRM\Leave\Dto\LeaveCommentSearchFilterParams;
use XHRM\Leave\Service\LeaveRequestCommentService;

class LeaveCommentAPI extends Endpoint implements CollectionEndpoint
{
    use DateTimeHelperTrait;
    use UserRoleManagerTrait;
    use EntityManagerHelperTrait;
    use AuthUserTrait;

    public const PARAMETER_LEAVE_ID = 'leaveId';
    public const PARAMETER_COMMENT = 'comment';

    public const PARAM_RULE_COMMENT_MAX_LENGTH = 255;
    /**
     * @var null|LeaveRequestCommentService
     */
    protected ?LeaveRequestCommentService $leaveRequestCommentService = null;

    /**
     * @return LeaveRequestCommentService
     */
    public function getLeaveRequestCommentService(): LeaveRequestCommentService
    {
        if (is_null($this->leaveRequestCommentService)) {
            $this->leaveRequestCommentService = new LeaveRequestCommentService();
        }
        return $this->leaveRequestCommentService;
    }

    /**
     * @return int|null
     */
    private function getUrlAttributes(): ?int
    {
        return $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_LEAVE_ID
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/leave/leaves/{leaveId}/leave-comments",
     *     tags={"Leave/Leave Comment"},
     *     summary="List All Comments for a Leave",
     *     operationId="list-all-comments-for-a-leave",
     *     @OA\PathParameter(
     *         name="leaveId",
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
     *                 ref="#/components/schemas/Leave-LeaveCommentModel"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        $leaveId = $this->getUrlAttributes();

        /** @var Leave|null $leave */
        $leave = $this->getLeaveRequestCommentService()->getLeaveRequestCommentDao()
            ->getLeaveById($leaveId);

        $this->throwRecordNotFoundExceptionIfNotExist($leave, Leave::class);

        $this->checkLeaveAccessible($leave);

        $leaveCommentSearchFilterParams = new LeaveCommentSearchFilterParams();

        $leaveCommentSearchFilterParams->setLeaveId($leaveId);
        $this->setSortingAndPaginationParams($leaveCommentSearchFilterParams);

        $leaveComments = $this->getLeaveRequestCommentService()->getLeaveRequestCommentDao()
            ->searchLeaveComments($leaveCommentSearchFilterParams);
        return new EndpointCollectionResult(
            LeaveCommentModel::class,
            $leaveComments,
            new ParameterBag(
                [
                    CommonParams::PARAMETER_TOTAL => $this->getLeaveRequestCommentService()->getLeaveRequestCommentDao()
                        ->getSearchLeaveCommentsCount($leaveCommentSearchFilterParams)
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            ...$this->getCommonValidationRules(),
            ...$this->getSortingAndPaginationParamsRules(LeaveCommentSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v2/leave/leaves/{leaveId}/leave-comments",
     *     tags={"Leave/Leave Comment"},
     *     summary="Comment on a Leave",
     *     operationId="comment-on-a-leave",
     *     @OA\PathParameter(
     *         name="leaveId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="comment", type="string", maxLength=XHRM\Leave\Api\LeaveCommentAPI::PARAM_RULE_COMMENT_MAX_LENGTH),
     *             required={"comment"}
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Leave-LeaveCommentModel"
     *             ),
     *             @OA\Property(property="meta", type="object"),
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function create(): EndpointResourceResult
    {
        $leaveId = $this->getUrlAttributes();

        /** @var Leave|null $leave */
        $leave = $this->getLeaveRequestCommentService()->getLeaveRequestCommentDao()->getLeaveById(
            $leaveId
        );

        $this->throwRecordNotFoundExceptionIfNotExist($leave, Leave::class);

        $this->checkLeaveAccessible($leave);

        $leaveComment = new LeaveComment();
        $leaveComment->getDecorator()->setLeaveById($leaveId);
        $this->setLeaveComment($leaveComment);
        $leaveComment = $this->saveLeaveComment($leaveComment);
        return new EndpointResourceResult(
            LeaveCommentModel::class,
            $leaveComment,
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMMENT,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMMENT_MAX_LENGTH]),
                ),
            ),
            ...$this->getCommonValidationRules()
        );
    }

    /**
     * @return ParamRule[]
     */
    private function getCommonValidationRules(): array
    {
        return [
            new ParamRule(
                self::PARAMETER_LEAVE_ID,
                new Rule(Rules::POSITIVE)
            )
        ];
    }

    /**
     * @param LeaveComment $leaveComment
     */
    private function setLeaveComment(LeaveComment $leaveComment): void
    {
        $comment = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_COMMENT
        );
        $leaveComment->setComment($comment);
        $leaveComment->setCreatedAt($this->getDateTimeHelper()->getNow());
        $leaveComment->getDecorator()->setCreatedByEmployeeByEmpNumber($this->getAuthUser()->getEmpNumber());
        $leaveComment->getDecorator()->setCreatedByUserById($this->getAuthUser()->getUserId());
    }

    /**
     * @param LeaveComment $leaveComment
     * @return LeaveComment
     * @throws Exception
     */
    private function saveLeaveComment(LeaveComment $leaveComment): LeaveComment
    {
        return $this->getLeaveRequestCommentService()
            ->getLeaveRequestCommentDao()
            ->saveLeaveComment($leaveComment);
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
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

    /**
     * @param Leave $leave
     * @throws ForbiddenException
     */
    protected function checkLeaveAccessible(Leave $leave): void
    {
        $empNumber = $leave->getEmployee()->getEmpNumber();
        if (
            !($this->getUserRoleManager()->isEntityAccessible(Employee::class, $empNumber) ||
                $this->getUserRoleManagerHelper()->isSelfByEmpNumber($empNumber))
        ) {
            throw $this->getForbiddenException();
        }
    }
}

