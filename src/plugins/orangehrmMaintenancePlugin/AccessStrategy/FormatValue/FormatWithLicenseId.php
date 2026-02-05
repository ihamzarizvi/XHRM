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

use XHRM\Entity\License;
use XHRM\Maintenance\FormatValueStrategy\ValueFormatter;
use XHRM\Admin\Service\LicenseService;

class FormatWithLicenseId implements ValueFormatter
{
    private ?LicenseService $licenseService = null;

    /**
     * @param $entityValue
     * @return null|string
     */
    public function getFormattedValue($entityValue): ?string
    {
        $license = $this->getLicenseService()->getLicenseById($entityValue);
        if ($license instanceof License) {
            return $license->getName();
        }
        return null;
    }

    /**
     * @return LicenseService
     */
    public function getLicenseService(): LicenseService
    {
        if (!($this->licenseService instanceof LicenseService)) {
            $this->licenseService = new LicenseService();
        }
        return $this->licenseService;
    }
}
