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

use XHRM\Core\Traits\EventDispatcherTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Dashboard\Service\ChartService;
use XHRM\Dashboard\Service\EmployeeActionSummaryService;
use XHRM\Dashboard\Service\EmployeeOnLeaveService;
use XHRM\Dashboard\Service\EmployeeTimeAtWorkService;
use XHRM\Dashboard\Service\QuickLaunchService;
use XHRM\Dashboard\Subscriber\BuzzModuleStatusChangeSubscriber;
use XHRM\Dashboard\Subscriber\LeaveModuleStatusChangeSubscriber;
use XHRM\Dashboard\Subscriber\TimeModuleStatusChangeSubscriber;
use XHRM\Framework\Http\Request;
use XHRM\Framework\PluginConfigurationInterface;
use XHRM\Framework\Services;

class DashboardPluginConfiguration implements PluginConfigurationInterface
{
    use ServiceContainerTrait;
    use EventDispatcherTrait;

    /**
     * @inheritDoc
     */
    public function initialize(Request $request): void
    {
        $this->getContainer()->register(
            Services::EMPLOYEE_ON_LEAVE_SERVICE,
            EmployeeOnLeaveService::class
        );

        $this->getContainer()->register(
            Services::CHART_SERVICE,
            ChartService::class
        );
        $this->getContainer()->register(
            Services::QUICK_LAUNCH_SERVICE,
            QuickLaunchService::class
        );

        $this->getContainer()->register(
            Services::EMPLOYEE_TIME_AT_WORK_SERVICE,
            EmployeeTimeAtWorkService::class
        );

        $this->getContainer()->register(
            Services::EMPLOYEE_ACTION_SUMMARY_SERVICE,
            EmployeeActionSummaryService::class
        );

        $this->getEventDispatcher()->addSubscriber(new TimeModuleStatusChangeSubscriber());
        $this->getEventDispatcher()->addSubscriber(new LeaveModuleStatusChangeSubscriber());
        $this->getEventDispatcher()->addSubscriber(new BuzzModuleStatusChangeSubscriber());
    }
}

