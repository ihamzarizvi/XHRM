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

namespace XHRM\Core\Authorization\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\HomePage;
use XHRM\Entity\ModuleDefaultPage;
use XHRM\ORM\ListSorter;

class HomePageDao extends BaseDao
{
    /**
     * Get home page records for the given user role ids in priority order. (Descending order of the priority field).
     * If two records have the same priority, the higher ID will be returned first. (Assuming the later entry was
     * intended to override the earlier entry).
     *
     * @param array $userRoleIds Array of user role ids
     * @return HomePage[] List of matching home page entries
     */
    public function getHomePagesInPriorityOrder(array $userRoleIds): array
    {
        $q = $this->createQueryBuilder(HomePage::class, 'h');
        $q->leftJoin('h.userRole', 'ur');
        $q->andWhere($q->expr()->in('ur.id', ':userRoleIds'))
            ->setParameter('userRoleIds', $userRoleIds);
        $q->addOrderBy('h.priority', ListSorter::DESCENDING);
        $q->addOrderBy('h.id', ListSorter::DESCENDING);

        return $q->getQuery()->execute();
    }

    /**
     * Get module default page records for the given module and given user role ids in priority order.
     * (Descending order of the priority field).
     * If two records have the same priority, the higher ID will be returned first. (Assuming the later entry was
     * intended to override the earlier entry).
     *
     * @param string $moduleName Module Name
     * @param array $userRoleIds Array of user role ids
     * @return ModuleDefaultPage[] List of matching default page entries
     */
    public function getModuleDefaultPagesInPriorityOrder(string $moduleName, array $userRoleIds): array
    {
        $q = $this->createQueryBuilder(ModuleDefaultPage::class, 'p');
        $q->leftJoin('p.module', 'm');
        $q->leftJoin('p.userRole', 'ur');
        $q->andWhere($q->expr()->in('ur.id', ':userRoleIds'))
            ->setParameter('userRoleIds', $userRoleIds);
        $q->andWhere('m.name = :moduleName')
            ->setParameter('moduleName', $moduleName);
        $q->addOrderBy('m.name', ListSorter::DESCENDING);
        $q->addOrderBy('p.priority', ListSorter::DESCENDING);

        return $q->getQuery()->execute();
    }
}
