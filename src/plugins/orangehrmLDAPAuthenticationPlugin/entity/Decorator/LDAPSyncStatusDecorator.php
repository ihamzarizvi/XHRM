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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\LDAPSyncStatus;
use XHRM\Entity\User;

class LDAPSyncStatusDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;

    protected LDAPSyncStatus $LDAPSyncStatus;

    /**
     * @param LDAPSyncStatus $LDAPSyncStatus
     */
    public function __construct(LDAPSyncStatus $LDAPSyncStatus)
    {
        $this->LDAPSyncStatus = $LDAPSyncStatus;
    }

    /**
     * @param int $userId
     */
    public function setSyncedUserByUserId(int $userId): void
    {
        $user = $this->getReference(User::class, $userId);
        $this->LDAPSyncStatus->setSyncedBy($user);
    }

    /**
     * @return string|null
     */
    public function getSyncStartedDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDate($this->LDAPSyncStatus->getSyncStartedAt());
    }

    /**
     * @return string|null
     */
    public function getSyncStartedTime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString($this->LDAPSyncStatus->getSyncStartedAt());
    }

    /**
     * @return string|null
     */
    public function getSyncFinishedDate(): ?string
    {
        return $this->getDateTimeHelper()->formatDate($this->LDAPSyncStatus->getSyncFinishedAt());
    }

    /**
     * @return string|null
     */
    public function getSyncFinishedTime(): ?string
    {
        return $this->getDateTimeHelper()->formatDateTimeToTimeString($this->LDAPSyncStatus->getSyncFinishedAt());
    }
}
