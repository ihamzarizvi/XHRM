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

namespace XHRM\Core\Registration\Processor;

use XHRM\Entity\RegistrationEventQueue;
use XHRM\Pim\Dto\EmployeeSearchFilterParams;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;

class RegistrationEmployeeActivationEventProcessor extends AbstractRegistrationEventProcessor
{
    use EmployeeServiceTrait;

    /**
     * @inheritDoc
     */
    public function getEventType(): int
    {
        return RegistrationEventQueue::ACTIVE_EMPLOYEE_COUNT;
    }

    /**
     * @inheritDoc
     */
    public function getEventData(): array
    {
        $registrationData = $this->getRegistrationEventGeneralData();
        $employeeSearchFilterParams = new EmployeeSearchFilterParams();
        $employeeSearchFilterParams->setIncludeEmployees(EmployeeSearchFilterParams::INCLUDE_EMPLOYEES_ONLY_CURRENT);
        $employeeCount = $this->getEmployeeService()->getEmployeeCount($employeeSearchFilterParams);
        $registrationData['employee_count'] = $employeeCount;
        return $registrationData;
    }

    /**
     * @inheritDoc
     */
    public function getEventToBeSavedOrNot(): bool
    {
        $employeeSearchFilterParams = new EmployeeSearchFilterParams();
        $employeeSearchFilterParams->setIncludeEmployees(EmployeeSearchFilterParams::INCLUDE_EMPLOYEES_ONLY_CURRENT);
        $employeeCount = $this->getEmployeeService()->getEmployeeCount($employeeSearchFilterParams);
        if ($employeeCount > 0 && $employeeCount % RegistrationEventQueue::EMPLOYEE_COUNT_CHANGE_TRACKER_SIZE == 0) {
            return true;
        }
        return false;
    }
}

