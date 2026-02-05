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

namespace XHRM\Maintenance\AccessStrategy\FormatValue;

use XHRM\Admin\Service\LocationService;
use XHRM\Entity\Location;
use XHRM\Maintenance\FormatValueStrategy\ValueFormatter;

class FormatWithLocationId implements ValueFormatter
{
    private ?LocationService $locationService = null;

    /**
     * @param $entityValue
     * @return string|null
     */
    public function getFormattedValue($entityValue): ?string
    {
        $location = $this->getLocationService()->getLocationById($entityValue);
        if ($location instanceof Location) {
            return $location->getName();
        }
        return null;
    }

    /**
     * @return LocationService
     */
    public function getLocationService(): ?LocationService
    {
        if (is_null($this->locationService)) {
            $this->locationService = new LocationService();
        }
        return $this->locationService;
    }
}
