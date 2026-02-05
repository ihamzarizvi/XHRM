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

namespace XHRM\OpenidAuthentication\Service;

use XHRM\Admin\Dao\UserDao;
use XHRM\Admin\Dto\UserSearchFilterParams;
use XHRM\Authentication\Dto\UserCredential;
use XHRM\Authentication\Exception\AuthenticationException;
use XHRM\Authentication\Service\AuthenticationService;
use XHRM\Authentication\Traits\Service\AuthenticationServiceTrait;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Utility\EncryptionHelperTrait;
use XHRM\Entity\AuthProviderExtraDetails;
use XHRM\Entity\EmployeeTerminationRecord;
use XHRM\Entity\OpenIdProvider;
use XHRM\Entity\OpenIdUserIdentity;
use XHRM\Entity\User;
use XHRM\Framework\Routing\UrlGenerator;
use XHRM\Framework\Services;
use XHRM\OpenidAuthentication\Dao\AuthProviderDao;
use XHRM\OpenidAuthentication\Dto\ProviderSearchFilterParams;
use XHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use XHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SocialMediaAuthenticationService
{
    use SocialMediaAuthenticationServiceTrait;
    use AuthenticationServiceTrait;
    use EncryptionHelperTrait;
    use AuthUserTrait;

    private AuthenticationService $authenticationService;
    private AuthProviderDao $authProviderDao;
    private UserDao $userDao;

    public const SCOPE = 'email';

    /**
     * @return AuthProviderDao
     */
    public function getAuthProviderDao(): AuthProviderDao
    {
        return $this->authProviderDao ??= new AuthProviderDao();
    }

    /**
     * @return UserDao
     */
    public function getUserDao(): UserDao
    {
        return $this->userDao ??= new UserDao();
    }

    /**
     * @param AuthProviderExtraDetails $provider
     * @param string $scope
     * @param string $redirectUrl
     *
     * @return OpenIDConnectClient
     */
    public function initiateAuthentication(AuthProviderExtraDetails $provider, string $scope, string $redirectUrl): OpenIDConnectClient
    {
        $oidcClient = new OpenIDConnectClient(
            $provider->getOpenIdProvider()->getProviderUrl(),
            $provider->getClientId(),
            self::encryptionEnabled()
            ? self::getCryptographer()->decrypt($provider->getClientSecret())
            : $provider->getClientSecret(),
        );

        $oidcClient->addScope([$scope]);
        $oidcClient->setRedirectURL($redirectUrl);

        return $oidcClient;
    }

    /**
     * @return string
     */
    public function getRedirectURL(): string
    {
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        return $urlGenerator->generate('auth_oidc_login_redirect', [], UrlGenerator::ABSOLUTE_URL);
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return self::SCOPE;
    }

    /**
     * @param UserCredential $userCredential
     * @return User[]
     */
    private function getSystemUsers(UserCredential $userCredential): array
    {
        $userSearchFilterParams = new UserSearchFilterParams();
        $userSearchFilterParams->setUsername($userCredential->getUsername());

        return $this->getUserDao()->searchSystemUsers($userSearchFilterParams);
    }

    /**
     * @param UserCredential $userCredentials
     *
     * @return User
     * @throws AuthenticationException
     */
    public function getUserForAuthenticate(UserCredential $userCredentials): User
    {
        $users = $this->getSystemUsers($userCredentials);
        if (empty($users)) {
            throw AuthenticationException::noUserFound();
        }

        if (sizeof($users) > 1) {
            throw AuthenticationException::multipleUserReturned();
        }

        $user = $users[0];

        if (!$user instanceof User || $user->isDeleted()) {
            throw AuthenticationException::invalidCredentials();
        } else {
            if (!$user->getStatus()) {
                throw AuthenticationException::userDisabled();
            } elseif ($user->getEmpNumber() === null) {
                throw AuthenticationException::employeeNotAssigned();
            } elseif ($user->getEmployee()->getEmployeeTerminationRecord() instanceof EmployeeTerminationRecord) {
                throw AuthenticationException::employeeTerminated();
            }
            return $user;
        }
    }

    /**
     * @param User $user
     * @param OpenIdProvider $provider
     *
     * @return OpenIdUserIdentity
     */
    public function setOIDCUserIdentity(User $user, OpenIdProvider $provider): OpenIdUserIdentity
    {
        $openIdUserIdentity = new OpenIdUserIdentity();
        $openIdUserIdentity->setUser($user);
        $openIdUserIdentity->setOpenIdProvider($provider);

        return $this->getAuthProviderDao()->saveUserIdentity($openIdUserIdentity);
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws AuthenticationException
     */
    public function handleOIDCAuthentication(User $user): bool
    {
        return $this->getAuthenticationService()->setCredentialsForUser($user);
    }

    /**
     * @return bool
     */
    public function isSocialMediaAuthEnable(): bool
    {
        $providerSearchFilterParams = new ProviderSearchFilterParams();
        $providerSearchFilterParams->setName(null);
        $providerSearchFilterParams->setStatus(true);

        $count = $this->getAuthProviderDao()->getAuthProviderCount($providerSearchFilterParams);
        return $count > 0;
    }
}

