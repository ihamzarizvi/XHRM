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

namespace XHRM\Pim\Api;

use XHRM\Admin\Api\Model\UserModel;
use XHRM\Admin\Traits\Service\UserServiceTrait;
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
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;

class UpdatePasswordAPI extends Endpoint implements ResourceEndpoint
{
    use UserRoleManagerTrait;
    use UserServiceTrait;
    use DateTimeHelperTrait;

    public const PARAMETER_CURRENT_PASSWORD = 'currentPassword';
    public const PARAMETER_NEW_PASSWORD = 'newPassword';

    public const PARAM_RULE_PASSWORD_MAX_LENGTH = 64;

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @OA\Put(
     *     path="/api/v2/pim/update-password",
     *     tags={"PIM/Update Password"},
     *     summary="Update Password",
     *     operationId="update-password",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="currentPassword",
     *                 type="string",
     *                 maxLength=XHRM\Pim\Api\UpdatePasswordAPI::PARAM_RULE_PASSWORD_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="newPassword",
     *                 type="string",
     *                 maxLength=XHRM\Pim\Api\UpdatePasswordAPI::PARAM_RULE_PASSWORD_MAX_LENGTH
     *             ),
     *             required={"currentPassword", "newPassword"},
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Admin-UserModel"
     *             ),
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $user = $this->getUserRoleManager()->getUser();
        $newPassword = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_NEW_PASSWORD
        );
        $user->getDecorator()->setNonHashedPassword($newPassword);
        $user->setDateModified($this->getDateTimeHelper()->getNow());
        $user = $this->getUserService()->saveSystemUser($user);
        return new EndpointResourceResult(UserModel::class, $user);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_ID
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(
                    self::PARAMETER_CURRENT_PASSWORD,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_PASSWORD_MAX_LENGTH]),
                    new Rule(Rules::CALLBACK, [
                        function () {
                            $currentPassword = $this->getRequestParams()->getString(
                                RequestParams::PARAM_TYPE_BODY,
                                self::PARAMETER_CURRENT_PASSWORD
                            );
                            $userId = $this->getUserRoleManager()->getUser()->getId();
                            return $this->getUserService()->isCurrentPassword($userId, $currentPassword);
                        }
                    ])
                )
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(
                    self::PARAMETER_NEW_PASSWORD,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_PASSWORD_MAX_LENGTH]),
                    new Rule(Rules::PASSWORD, [true])
                ),
            ),
        );
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

