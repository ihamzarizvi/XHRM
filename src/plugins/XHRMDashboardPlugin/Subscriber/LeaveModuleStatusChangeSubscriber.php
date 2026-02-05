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

namespace XHRM\Dashboard\Subscriber;

use XHRM\Core\Event\ModuleEvent;
use XHRM\Core\Event\ModuleStatusChange;
use XHRM\Dashboard\Traits\Service\ModuleServiceTrait;
use XHRM\Framework\Event\AbstractEventSubscriber;

class LeaveModuleStatusChangeSubscriber extends AbstractEventSubscriber
{
    use ModuleServiceTrait;

    public const MODULE_LEAVE = 'leave';

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ModuleEvent::MODULE_STATUS_CHANGE => [['onStatusChangeEvent', 0]]
        ];
    }

    /**
     * @param ModuleStatusChange $moduleStatusChange
     */
    public function onStatusChangeEvent(ModuleStatusChange $moduleStatusChange)
    {
        $previousModule = $moduleStatusChange->getPreviousModule();
        $currentModule = $moduleStatusChange->getCurrentModule();

        if ($previousModule->getName() === self::MODULE_LEAVE) {
            $this->getModuleService()
                ->getModuleDao()
                ->updateDataGroupPermissionForWidgetModules('dashboard_leave_widget', $currentModule->getStatus());
        }
    }
}

