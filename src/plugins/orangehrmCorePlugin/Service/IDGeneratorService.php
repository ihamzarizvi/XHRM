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

namespace XHRM\Core\Service;

use XHRM\Core\Dao\IDGeneratorDao;
use XHRM\Core\Traits\ClassHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\JobCategory;
use XHRM\Entity\JobTitle;
use XHRM\Entity\Location;
use XHRM\Entity\Membership;

class IDGeneratorService
{
    use ClassHelperTrait;

    public const MIN_LENGTH = 3;

    protected ?IDGeneratorDao $iDGeneratorDao = null;

    /**
     * @return IDGeneratorDao
     */
    public function getIDGeneratorDao(): IDGeneratorDao
    {
        if (!$this->iDGeneratorDao instanceof IDGeneratorDao) {
            $this->iDGeneratorDao = new IDGeneratorDao();
        }
        return $this->iDGeneratorDao;
    }

    /**
     * @param IDGeneratorDao $iDGeneratorDao
     */
    public function setIDGeneratorDao(IDGeneratorDao $iDGeneratorDao): void
    {
        $this->iDGeneratorDao = $iDGeneratorDao;
    }

    /**
     * get the prefix
     * @param string $entityClass
     * @return string
     */
    private function getEntityPrefix(string $entityClass): string
    {
        $prefix = '';
        $entityClass = $this->getClassHelper()->getClass($entityClass, 'XHRM\\Entity\\');
        switch ($entityClass) {
            case Location::class:
                $prefix = 'LOC';
                break;

            case JobCategory::class:
                $prefix = 'EEC';
                break;

            case JobTitle::class:
                $prefix = 'JOB';
                break;

            case Membership::class:
                $prefix = 'MME';
                break;
        }

        return $prefix;
    }

    /**
     * Get next auto increment ID
     *
     * @param string $entityClass
     * @param bool update - Update id in the database - defaults to true.
     * @return string auto increment ID
     */
    public function getNextID(string $entityClass, $update = true): string
    {
        $prefix = $this->getEntityPrefix($entityClass);

        $currentId = $this->getIDGeneratorDao()->getCurrentID($entityClass);

        if ($update) {
            $this->getIDGeneratorDao()->updateNextId($entityClass, $currentId + 1);
        }

        $minLength = self::MIN_LENGTH;
        if ($entityClass == Employee::class) {
            $minLength = 4;
        }
        return $prefix . str_pad($currentId + 1, $minLength, "0", STR_PAD_LEFT);
    }

    /**
     * @param string $entityClass
     */
    public function incrementId(string $entityClass): void
    {
        $currentId = $this->getIDGeneratorDao()->getCurrentID($entityClass);
        $this->getIDGeneratorDao()->updateNextId($entityClass, $currentId + 1);
    }
}
