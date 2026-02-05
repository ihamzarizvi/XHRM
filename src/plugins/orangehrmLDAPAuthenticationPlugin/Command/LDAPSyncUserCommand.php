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

namespace XHRM\LDAP\Command;

use DateTimeZone;
use XHRM\Core\Service\DateTimeHelperService;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\LDAPSyncStatus;
use XHRM\Framework\Console\Command;
use XHRM\LDAP\Dto\LDAPSetting;
use XHRM\LDAP\Service\LDAPSyncService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class LDAPSyncUserCommand extends Command
{
    use ConfigServiceTrait;
    use DateTimeHelperTrait;

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'orangehrm:ldap-sync-user';
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Sync users from LDAP');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ldapSetting = $this->getConfigService()->getLDAPSetting();
        if (!$ldapSetting instanceof LDAPSetting) {
            $this->getIO()->error('LDAP settings not configured');
            return self::FAILURE;
        }
        if (!$ldapSetting->isEnable()) {
            $this->getIO()->error('LDAP sync not enabled');
            return self::FAILURE;
        }

        $ldapSyncStatus = new LDAPSyncStatus();
        $ldapSyncService = new LDAPSyncService();
        try {
            $ldapSyncStatus->setSyncedBy(null);
            $ldapSyncStatus->setSyncStartedAt(
                $this->getDateTimeHelper()->getNow()
                    ->setTimezone(new DateTimeZone(DateTimeHelperService::TIMEZONE_UTC))
            );
            $ldapSyncService->sync();
            $ldapSyncStatus->setSyncFinishedAt(
                $this->getDateTimeHelper()->getNow()
                    ->setTimezone(new DateTimeZone(DateTimeHelperService::TIMEZONE_UTC))
            );
            $ldapSyncStatus->setSyncStatus(LDAPSyncStatus::SYNC_STATUS_SUCCEEDED);
            $ldapSyncService->getLDAPDao()->saveLdapSyncStatus($ldapSyncStatus);
        } catch (Throwable $exception) {
            $ldapSyncStatus->setSyncStatus(LDAPSyncStatus::SYNC_STATUS_FAILED);
            $ldapSyncService->getLDAPDao()->saveLdapSyncStatus($ldapSyncStatus);
            $this->getIO()->error('Please check the settings for your LDAP configuration');
            return self::FAILURE;
        }
        $this->getIO()->success('Success');
        return self::SUCCESS;
    }
}
