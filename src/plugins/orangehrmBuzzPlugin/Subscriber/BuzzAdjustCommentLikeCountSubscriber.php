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

namespace XHRM\Buzz\Subscriber;

use XHRM\Buzz\Traits\Service\BuzzServiceTrait;
use XHRM\Framework\Event\AbstractEventSubscriber;
use XHRM\Maintenance\Event\MaintenanceEvent;
use XHRM\Maintenance\Event\PurgeEmployee;
use XHRM\Pim\Event\EmployeeDeletedEvent;
use XHRM\Pim\Event\EmployeeEvents;

class BuzzAdjustCommentLikeCountSubscriber extends AbstractEventSubscriber
{
    use BuzzServiceTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MaintenanceEvent::PURGE_EMPLOYEE_END => 'onEmployeePurgingEnd',
            EmployeeEvents::EMPLOYEES_DELETED => 'onEmployeesDeleted',
        ];
    }

    /**
     * @param PurgeEmployee $purgeEmployee
     */
    public function onEmployeePurgingEnd(PurgeEmployee $purgeEmployee): void
    {
        $this->getBuzzService()->getBuzzDao()->adjustLikeAndCommentCountsOnShares();
        $this->getBuzzService()->getBuzzDao()->adjustLikeCountOnComments();
    }

    /**
     * @param EmployeeDeletedEvent $event
     */
    public function onEmployeesDeleted(EmployeeDeletedEvent $event): void
    {
        $this->getBuzzService()->getBuzzDao()->adjustLikeAndCommentCountsOnShares();
        $this->getBuzzService()->getBuzzDao()->adjustLikeCountOnComments();
    }
}
