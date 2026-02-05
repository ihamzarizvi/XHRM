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

namespace XHRM\Authentication\Controller;

use XHRM\Admin\Service\UserService;
use XHRM\Admin\Traits\Service\UserServiceTrait;
use XHRM\Authentication\Auth\User as AuthUser;
use XHRM\Authentication\Dto\UserCredential;
use XHRM\Authentication\Exception\AuthenticationException;
use XHRM\Authentication\Traits\CsrfTokenManagerTrait;
use XHRM\Authentication\Traits\Service\PasswordStrengthServiceTrait;
use XHRM\Authentication\Utility\PasswordStrengthValidation;
use XHRM\Core\Api\V2\Exception\InvalidParamException;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Api\V2\Validator\ValidatorException;
use XHRM\Core\Traits\LoggerTrait;
use XHRM\Core\Traits\ValidatorTrait;
use XHRM\Framework\Http\Response;
use XHRM\Core\Controller\AbstractController;
use XHRM\Core\Controller\PublicControllerInterface;
use XHRM\Core\Service\ConfigService;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\Service\TextHelperTrait;
use XHRM\Entity\User;
use XHRM\Framework\Http\RedirectResponse;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Routing\UrlGenerator;
use XHRM\Framework\Services;
use XHRM\I18N\Traits\Service\I18NHelperTrait;

class RequestResetWeakPasswordController extends AbstractController implements PublicControllerInterface
{
    use PasswordStrengthServiceTrait;
    use CsrfTokenManagerTrait;
    use UserServiceTrait;
    use AuthUserTrait;
    use I18NHelperTrait;
    use ConfigServiceTrait;
    use TextHelperTrait;
    use LoggerTrait;
    use EntityManagerHelperTrait;
    use ValidatorTrait;
    use PasswordStrengthServiceTrait;

    public const PARAMETER_CURRENT_PASSWORD = 'currentPassword';
    public const PARAMETER_USERNAME = 'username';
    public const PARAMETER_PASSWORD = 'password';
    public const PARAMETER_RESET_CODE = 'resetCode';

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function handle(Request $request)
    {
        $currentPassword = $request->request->get('currentPassword');
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $resetCode = $request->request->get('resetCode');
        $token = $request->request->get('_token');

        $user = $this->getUserService()->geUserDao()->getUserByUserName($username);

        if (!$this->validateParameters($request)) {
            return $this->handleBadRequest();
        }

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $redirectUrl = $urlGenerator->generate(
            'auth_weak_password_reset',
            ['resetCode' => $resetCode],
            UrlGenerator::ABSOLUTE_URL
        );

        if (!$this->getCsrfTokenManager()->isValid('reset-weak-password', $token)) {
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_PASSWORD_ENFORCE_ERROR,
                [
                    'error' => AuthenticationException::INVALID_CSRF_TOKEN,
                    'message' => $this->getI18NHelper()->trans('csrf_token_validation_failed'),
                ]
            );
            return new RedirectResponse($redirectUrl);
        }

        if (!$this->getPasswordStrengthService()->validateUrl($resetCode)) {
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_PASSWORD_ENFORCE_ERROR,
                [
                    'error' => AuthenticationException::INVALID_RESET_CODE,
                    'message' => $this->getI18NHelper()->trans('auth.invalid_password_reset_code')
                ]
            );
            return new RedirectResponse($redirectUrl);
        }

        if (!$user instanceof User || !$this->getUserService()->isCurrentPassword($user->getId(), $currentPassword)) {
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_PASSWORD_ENFORCE_ERROR,
                [
                    'error' => AuthenticationException::INVALID_CREDENTIALS,
                    'message' => $this->getI18NHelper()->trans('auth.invalid_credentials'),
                ]
            );
            return new RedirectResponse($redirectUrl);
        } else {
            $credentials = new UserCredential($username, $password);
            $this->getPasswordStrengthService()->saveEnforcedPassword($credentials);
            return $this->redirect("auth/login");
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function validateParameters(Request $request): bool
    {
        $variables = $request->request->all();

        $paramRules = $this->getParamRuleCollection();
        $paramRules->addExcludedParamKey('confirmPassword');
        $paramRules->addExcludedParamKey('_token');

        try {
            $credentials = new UserCredential();
            $credentials->setPassword($request->request->get('password'));
            $credentials->setUsername($request->request->get('username'));
            $passwordStrengthValidation = new PasswordStrengthValidation();
            $passwordStrength = $passwordStrengthValidation->checkPasswordStrength($credentials);

            if (!$this->getPasswordStrengthService()->isValidPassword($credentials, $passwordStrength)) {
                return false;
            }

            return $this->validate($variables, $paramRules);
        } catch (InvalidParamException|ValidatorException $e) {
            $this->getLogger()->warning($e->getMessage());
            return false;
        }
    }

    /**
     * @return ParamRuleCollection|null
     */
    private function getParamRuleCollection(): ?ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_USERNAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [
                    UserService::USERNAME_MIN_LENGTH,
                    UserService::USERNAME_MAX_LENGTH
                ])
            ),
            new ParamRule(
                self::PARAMETER_RESET_CODE,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::NOT_BLANK),
            ),
            new ParamRule(
                self::PARAMETER_PASSWORD,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [
                    null,
                    ConfigService::MAX_PASSWORD_LENGTH
                ]),
            ),
            new ParamRule(
                self::PARAMETER_CURRENT_PASSWORD,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [
                    null,
                    ConfigService::MAX_PASSWORD_LENGTH
                ]),
            ),
        );
    }
}

