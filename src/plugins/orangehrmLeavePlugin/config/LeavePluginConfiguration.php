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

use XHRM\Core\Traits\EventDispatcherTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Http\Request;
use XHRM\Framework\PluginConfigurationInterface;
use XHRM\Framework\Services;
use XHRM\Leave\Service\HolidayService;
use XHRM\Leave\Service\LeaveConfigurationService;
use XHRM\Leave\Service\LeaveEntitlementService;
use XHRM\Leave\Service\LeavePeriodService;
use XHRM\Leave\Service\LeaveRequestService;
use XHRM\Leave\Service\LeaveTypeService;
use XHRM\Leave\Service\WorkScheduleService;
use XHRM\Leave\Service\WorkWeekService;
use XHRM\Leave\Subscriber\LeaveEventSubscriber;

class LeavePluginConfiguration implements PluginConfigurationInterface
{
    use ServiceContainerTrait;
    use EventDispatcherTrait;

    /**
     * @inheritDoc
     */
    public function initialize(Request $request): void
    {
        $this->getContainer()->register(
            Services::LEAVE_CONFIG_SERVICE,
            LeaveConfigurationService::class
        );
        $this->getContainer()->register(
            Services::LEAVE_TYPE_SERVICE,
            LeaveTypeService::class
        );
        $this->getContainer()->register(
            Services::LEAVE_ENTITLEMENT_SERVICE,
            LeaveEntitlementService::class
        );
        $this->getContainer()->register(
            Services::LEAVE_PERIOD_SERVICE,
            LeavePeriodService::class
        );
        $this->getContainer()->register(
            Services::LEAVE_REQUEST_SERVICE,
            LeaveRequestService::class
        );
        $this->getContainer()->register(
            Services::WORK_SCHEDULE_SERVICE,
            WorkScheduleService::class
        );
        $this->getContainer()->register(
            Services::HOLIDAY_SERVICE,
            HolidayService::class
        );
        $this->getContainer()->register(
            Services::WORK_WEEK_SERVICE,
            WorkWeekService::class
        );

        $this->getEventDispatcher()->addSubscriber(new LeaveEventSubscriber());
    }
}
