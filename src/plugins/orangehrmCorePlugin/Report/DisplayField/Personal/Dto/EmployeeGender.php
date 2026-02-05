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

namespace XHRM\Core\Report\DisplayField\Personal\Dto;

use XHRM\Core\Report\DisplayField\Stringable;
use XHRM\Entity\Employee;

class EmployeeGender implements Stringable
{
    private ?string $gender = null;

    /**
     * @param int|null $gender
     */
    public function __construct(?int $gender)
    {
        $this->gender = $gender;
    }

    /**
     * @inheritDoc
     */
    public function toString(): ?string
    {
        switch ($this->gender) {
            case Employee::GENDER_MALE:
                return 'Male';
            case Employee::GENDER_FEMALE:
                return 'Female';
            case Employee::GENDER_OTHER:
                return 'Other';
            default:
                return null;
        }
    }
}
