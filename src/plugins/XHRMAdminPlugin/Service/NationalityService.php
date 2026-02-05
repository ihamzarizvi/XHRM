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

use XHRM\Admin\Dao\NationalityDao;
use XHRM\Admin\Dto\NationalitySearchFilterParams;
use XHRM\Admin\Service\Model\NationalityModel;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Entity\Nationality;

class NationalityService
{
    use NormalizerServiceTrait;

    /**
     * @var NationalityDao|null
     */
    private ?NationalityDao $nationalityDao = null;

    /**
     * @return NationalityDao
     */
    public function getNationalityDao(): NationalityDao
    {
        if (!$this->nationalityDao instanceof NationalityDao) {
            $this->nationalityDao = new NationalityDao();
        }
        return $this->nationalityDao;
    }

    /**
     * @param NationalityDao $nationalityDao
     */
    public function setNationalityDao(NationalityDao $nationalityDao): void
    {
        $this->nationalityDao = $nationalityDao;
    }

    /**
     * @param NationalitySearchFilterParams $nationalitySearchParamHolder
     * @return array
     */
    public function getNationalityList(NationalitySearchFilterParams $nationalitySearchParamHolder): array
    {
        return $this->getNationalityDao()->getNationalityList($nationalitySearchParamHolder);
    }

    /**
     * @param NationalitySearchFilterParams $nationalitySearchParamHolder
     * @return int
     */
    public function getNationalityCount(NationalitySearchFilterParams $nationalitySearchParamHolder): int
    {
        return $this->getNationalityDao()->getNationalityCount($nationalitySearchParamHolder);
    }

    /**
     * @param Nationality $nationality
     * @return Nationality
     */
    public function saveNationality(Nationality $nationality): Nationality
    {
        return $this->getNationalityDao()->saveNationality($nationality);
    }

    /**
     * @param int $id
     * @return Nationality|null
     */
    public function getNationalityById(int $id): ?Nationality
    {
        return $this->getNationalityDao()->getNationalityById($id);
    }

    /**
     * @param string $name
     * @return Nationality|null
     */
    public function getNationalityByName(string $name): ?Nationality
    {
        return $this->getNationalityDao()->getNationalityByName($name);
    }

    /**
     * @param array $toDeleteIds
     * @return int
     */
    public function deleteNationalities(array $toDeleteIds): int
    {
        return $this->getNationalityDao()->deleteNationalities($toDeleteIds);
    }

    /**
     * @param string $nationalityName
     * @return bool
     */
    public function isExistingNationalityName(string $nationalityName): bool
    {
        return $this->getNationalityDao()->isExistingNationalityName($nationalityName);
    }

    /**
     * @return array
     */
    public function getNationalityArray(): array
    {
        $nationalitySearchParamHolder = new NationalitySearchFilterParams();
        $nationalitySearchParamHolder->setLimit(0);
        $nationalities = $this->getNationalityList($nationalitySearchParamHolder);
        return $this->getNormalizerService()->normalizeArray(NationalityModel::class, $nationalities);
    }
}

