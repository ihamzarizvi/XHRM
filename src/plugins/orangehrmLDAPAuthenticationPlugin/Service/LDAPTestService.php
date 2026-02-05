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

namespace XHRM\LDAP\Service;

use Exception;
use XHRM\Authentication\Dto\UserCredential;
use XHRM\LDAP\Dto\LDAPSetting;
use XHRM\LDAP\Traits\LDAPLoggerTrait;
use Symfony\Component\Ldap\Adapter\AdapterInterface;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;

class LDAPTestService extends LDAPService
{
    use LDAPLoggerTrait;

    public const STATUS_FAIL = 0;
    public const STATUS_SUCCESS = 1;

    private ?LDAPSetting $ldapSetting;

    /**
     * @param LDAPSetting|null $ldapSetting
     */
    public function __construct(?LDAPSetting $ldapSetting)
    {
        $this->ldapSetting = $ldapSetting;
    }

    /**
     * @inheritDoc
     */
    public function getAdapter(): AdapterInterface
    {
        if (!$this->adapter instanceof AdapterInterface && $this->ldapSetting instanceof LDAPSetting) {
            $this->adapter = new Adapter([
                'host' => $this->ldapSetting->getHost(),
                'port' => $this->ldapSetting->getPort(),
                'encryption' => $this->ldapSetting->getEncryption(),
                'version' => $this->ldapSetting->getVersion(),
                'referrals' => $this->ldapSetting->isOptReferrals(),
            ]);
        }
        return $this->adapter;
    }

    public function testConnection(): void
    {
        $this->bind(
            new UserCredential(
                $this->ldapSetting->getBindUserDN(),
                $this->ldapSetting->getBindUserPassword()
            )
        );
    }

    /**
     * @return array
     */
    public function testAuthentication(): array
    {
        try {
            $this->testConnection();
            return [
                'message' => 'Ok',
                'status' => self::STATUS_SUCCESS
            ];
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
            $this->getLogger()->error($exception->getTraceAsString());
            return [
                'message' => $exception->getMessage(),
                'status' => self::STATUS_FAIL
            ];
        }
    }
}
