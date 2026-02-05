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

namespace XHRM\OAuth\Service;

use XHRM\Entity\OAuthClient;
use XHRM\OAuth\Dao\OAuthClientDao;

class OAuthService
{
    public const PUBLIC_MOBILE_CLIENT_ID = 'orangehrm_mobile_app';

    /**
     * @var OAuthClientDao|null
     */
    private ?OAuthClientDao $oauthClientDao = null;

    /**
     * @return OAuthClientDao
     */
    public function getOAuthClientDao(): OAuthClientDao
    {
        return $this->oauthClientDao ??= new OAuthClientDao();
    }

    /**
     * @param bool $enabled
     */
    public function updateMobileClientStatus(bool $enabled): void
    {
        $client = $this->getOAuthClientDao()->getOAuthClientByClientId(self::PUBLIC_MOBILE_CLIENT_ID);
        if ($client instanceof OAuthClient) {
            $client->setEnabled($enabled);
            $this->getOAuthClientDao()->saveOAuthClient($client);
        }
    }

    /**
     * @return bool
     */
    public function getMobileClientStatus(): bool
    {
        $client = $this->getOAuthClientDao()->getOAuthClientByClientId(self::PUBLIC_MOBILE_CLIENT_ID);
        return $client instanceof OAuthClient && $client->isEnabled();
    }

    /**
     * @return int|null
     */
    public function getMobileClientId(): ?int
    {
        $client = $this->getOAuthClientDao()->getOAuthClientByClientId(self::PUBLIC_MOBILE_CLIENT_ID);
        return $client ? $client->getId() : null;
    }
}
