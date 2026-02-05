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

namespace XHRM\Admin\Service;

use XHRM\Admin\Dao\CountryDao;
use XHRM\Admin\Service\Model\CountryModel;
use XHRM\Admin\Service\Model\ProvinceModel;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Entity\Country;
use XHRM\Entity\Province;

class CountryService
{
    use NormalizerServiceTrait;

    /**
     * @var CountryDao|null
     */
    private ?CountryDao $countryDao = null;

    /**
     * @return CountryDao
     */
    public function getCountryDao(): CountryDao
    {
        if (is_null($this->countryDao)) {
            $this->countryDao = new CountryDao();
        }
        return $this->countryDao;
    }

    /**
     * @param CountryDao $countryDao
     */
    public function setCountryDao(CountryDao $countryDao): void
    {
        $this->countryDao = $countryDao;
    }

    /**
     * Get Country list
     * @return Country[]
     */
    public function getCountryList(): array
    {
        return $this->getCountryDao()->getCountryList();
    }

    /**
     *
     * @return Province[]
     */
    public function getProvinceList(): array
    {
        return $this->getCountryDao()->getProvinceList();
    }

    /**
     * Get Country By Country Name
     * @param string $countryName
     * @return Country|null
     */
    public function getCountryByCountryName(string $countryName): ?Country
    {
        return $this->getCountryDao()->getCountryByCountryName($countryName);
    }

    /**
     * Get country by country code
     *
     * @param string $countryCode
     * @return Country|null
     */
    public function getCountryByCountryCode(string $countryCode): ?Country
    {
        return $this->getCountryDao()->getCountryByCountryCode($countryCode);
    }

    /**
     * @param string $provinceCode
     * @return Province|null
     */
    public function getProvinceByProvinceCode(string $provinceCode): ?Province
    {
        return $this->getCountryDao()->getProvinceByProvinceCode($provinceCode);
    }

    /**
     * @return array
     */
    public function getCountryArray(): array
    {
        $countries = $this->getCountryList();
        return $this->getNormalizerService()->normalizeArray(CountryModel::class, $countries);
    }

    /**
     * @return array
     */
    public function getProvinceArray(): array
    {
        $provinces = $this->getProvinceList();
        return $this->getNormalizerService()->normalizeArray(ProvinceModel::class, $provinces);
    }
}

