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

namespace XHRM\OAuth\Server;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\OAuth\Dto\CryptKey;
use XHRM\OAuth\Repository\AccessTokenRepository;
use XHRM\OAuth\Repository\AuthorizationCodeRepository;
use XHRM\OAuth\Repository\ClientRepository;
use XHRM\OAuth\Repository\RefreshTokenRepository;
use XHRM\OAuth\Repository\ScopeRepository;

class OAuthServer
{
    use ConfigServiceTrait;

    private ?AuthorizationServer $oauthServer = null;
    private ClientRepository $clientRepository;
    private ScopeRepository $scopeRepository;
    private AccessTokenRepository $accessTokenRepository;
    private AuthorizationCodeRepository $authCodeRepository;
    private RefreshTokenRepository $refreshTokenRepository;
    private string $encryptionKey;
    private ?DateInterval $authCodeTTL = null;
    private ?DateInterval $refreshTokenTTL = null;
    private ?DateInterval $accessTokenTTL = null;

    private function init(): void
    {
        $this->encryptionKey = $this->getConfigService()->getOAuthEncryptionKey();
        $tokenEncryptionKey = $this->getConfigService()->getOAuthTokenEncryptionKey();
        $this->clientRepository = new ClientRepository();
        $this->scopeRepository = new ScopeRepository();
        $this->accessTokenRepository = new AccessTokenRepository();
        $this->accessTokenRepository->setEncryptionKey($tokenEncryptionKey);
        $this->authCodeRepository = new AuthorizationCodeRepository();
        $this->refreshTokenRepository = new RefreshTokenRepository();
        $this->authCodeTTL = $this->getConfigService()->getOAuthAuthCodeTTL();
        $this->refreshTokenTTL = $this->getConfigService()->getOAuthRefreshTokenTTL();
        $this->accessTokenTTL = $this->getConfigService()->getOAuthAccessTokenTTL();
    }

    /**
     * @return AuthorizationServer
     */
    public function getServer(): AuthorizationServer
    {
        if (!$this->oauthServer instanceof AuthorizationServer) {
            $this->init();
            $this->oauthServer = new AuthorizationServer(
                $this->clientRepository,
                $this->accessTokenRepository,
                $this->scopeRepository,
                new CryptKey(), // We are using opaque token, not JWT
                $this->encryptionKey
            );

            $grant = new AuthCodeGrant($this->authCodeRepository, $this->refreshTokenRepository, $this->authCodeTTL);
            $grant->setRefreshTokenTTL($this->refreshTokenTTL);

            $refreshTokenGrant = new RefreshTokenGrant($this->refreshTokenRepository);
            $refreshTokenGrant->setRefreshTokenTTL($this->refreshTokenTTL);

            $this->oauthServer->enableGrantType($grant, $this->accessTokenTTL);
            $this->oauthServer->enableGrantType($refreshTokenGrant, $this->accessTokenTTL);
        }
        return $this->oauthServer;
    }

    /**
     * @return DateInterval
     */
    public function getAuthCodeTTL(): DateInterval
    {
        $this->authCodeTTL ?? $this->getServer();
        return $this->authCodeTTL;
    }

    /**
     * @return DateInterval
     */
    public function getRefreshTokenTTL(): DateInterval
    {
        $this->refreshTokenTTL ?? $this->getServer();
        return $this->refreshTokenTTL;
    }

    /**
     * @return DateInterval
     */
    public function getAccessTokenTTL(): DateInterval
    {
        $this->accessTokenTTL ?? $this->getServer();
        return $this->accessTokenTTL;
    }
}
