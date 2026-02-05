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

namespace XHRM\Core\Report\DisplayField\ContactDetail\Dto;

use XHRM\Core\Report\DisplayField\Stringable;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Country;

class EmployeeAddress implements Stringable
{
    use EntityManagerHelperTrait;

    public const SEPARATOR = ', ';

    private ?string $street1 = null;
    private ?string $street2 = null;
    private ?string $city = null;
    private ?string $province = null;
    private ?string $zipcode = null;
    private ?string $country = null;

    /**
     * @param string|null $street1
     * @param string|null $street2
     * @param string|null $city
     * @param string|null $province
     * @param string|null $zipcode
     * @param string|null $country
     */
    public function __construct(
        ?string $street1,
        ?string $street2,
        ?string $city,
        ?string $province,
        ?string $zipcode,
        ?string $country
    ) {
        $this->street1 = $street1;
        $this->street2 = $street2;
        $this->city = $city;
        $this->province = $province;
        $this->zipcode = $zipcode;
        $this->country = $country;
    }

    /**
     * @inheritDoc
     */
    public function toString(): ?string
    {
        $properties = [
            $this->street1,
            $this->street2,
            $this->city,
            $this->province,
            $this->zipcode,
        ];

        if (!empty($this->country)) {
            $country = $this->getRepository(Country::class)->find($this->country);
            if ($country instanceof Country) {
                $properties[] = $country->getCountryName();
            }
        }
        return implode(
            self::SEPARATOR,
            array_filter($properties, fn(?string $property) => !empty($property))
        );
    }
}
