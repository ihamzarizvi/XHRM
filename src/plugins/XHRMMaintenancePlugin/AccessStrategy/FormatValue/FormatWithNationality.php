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

namespace XHRM\Maintenance\AccessStrategy\FormatValue;

use XHRM\Entity\Nationality;
use XHRM\Maintenance\FormatValueStrategy\ValueFormatter;
use XHRM\Admin\Service\NationalityService;

class FormatWithNationality implements ValueFormatter
{
    private ?NationalityService $nationalityService = null;

    /**
     * @param $entityValue
     * @return string|null
     */
    public function getFormattedValue($entityValue): ?string
    {
        $nationality = $this->getNationalityService()->getNationalityById($entityValue);
        if ($nationality instanceof Nationality) {
            return $nationality->getName();
        }
        return null;
    }

    /**
     * @return NationalityService
     */
    public function getNationalityService(): ?NationalityService
    {
        if (is_null($this->nationalityService)) {
            $this->nationalityService = new NationalityService();
        }
        return $this->nationalityService;
    }
}

