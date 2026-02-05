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

namespace XHRM\Authentication\PublicApi;

use XHRM\Authentication\Dto\UserCredential;
use XHRM\Authentication\Traits\Service\PasswordStrengthServiceTrait;
use XHRM\Authentication\Utility\PasswordStrengthValidation;
use XHRM\Core\Api\V2\CollectionEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\I18N\Traits\Service\I18NHelperTrait;

class PasswordStrengthValidationAPI extends Endpoint implements CollectionEndpoint
{
    use ConfigServiceTrait;
    use I18NHelperTrait;
    use PasswordStrengthServiceTrait;

    public const PARAMETER_PASSWORD = 'password';
    public const PARAMETER_PASSWORD_STRENGTH = 'strength';
    public const PARAMETER_MESSAGES = 'messages';

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/validation/password",
     *     tags={"Authentication/Password Strength"},
     *     summary="Validate Password Strength",
     *     operationId="validate-password-strength",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="password", type="string"),
     *             required={"name"}
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="messages", type="array",
     *                     @OA\Items(),
     *                     example="Your password must contain minimum 1 upper-case letter"
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="strength", type="integer")
     *             )
     *         )
     *     )
     * )
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        $password = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PASSWORD);
        $credentials = new UserCredential(null, $password);

        $passwordStrengthValidation = new PasswordStrengthValidation();

        $passwordStrength = $passwordStrengthValidation->checkPasswordStrength($credentials);
        $messages = $this->getPasswordStrengthService()->checkPasswordPolicies($credentials, $passwordStrength);

        if (count($messages) > 0 && $passwordStrength > PasswordStrengthValidation::BETTER) {
            $passwordStrength = PasswordStrengthValidation::BETTER;
        }

        return new EndpointResourceResult(
            ArrayModel::class,
            [self::PARAMETER_MESSAGES => $messages],
            new ParameterBag([self::PARAMETER_PASSWORD_STRENGTH => $passwordStrength])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_PASSWORD,
                new Rule(Rules::STRING_TYPE),
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

