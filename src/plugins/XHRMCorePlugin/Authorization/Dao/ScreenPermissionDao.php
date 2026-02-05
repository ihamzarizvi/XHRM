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

namespace XHRM\Core\Authorization\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\ScreenPermission;
use XHRM\Entity\UserRole;

class ScreenPermissionDao extends BaseDao
{
    /**
     * @param string $module Module Name
     * @param string $actionUrl Action
     * @param string[]|UserRole[] $roles Array of UserRole objects or user role names
     * @return ScreenPermission[]
     */
    public function getScreenPermissions(string $module, string $actionUrl, array $roles): array
    {
        $roleNames = [];

        foreach ($roles as $role) {
            if ($role instanceof UserRole) {
                $roleNames[] = $role->getName();
            } elseif (is_string($role)) {
                $roleNames[] = $role;
            }
        }

        $q = $this->createQueryBuilder(ScreenPermission::class, 'sp');
        $q->leftJoin('sp.userRole', 'ur');
        $q->leftJoin('sp.screen', 's');
        $q->leftJoin('s.module', 'm');
        $q->andWhere('m.name = :moduleName')
            ->setParameter('moduleName', $module);
        $q->andWhere('s.actionUrl = :actionUrl')
            ->setParameter('actionUrl', $actionUrl);
        $q->andWhere($q->expr()->in('ur.name', ':userRoleNames'))
            ->setParameter('userRoleNames', $roleNames);

        return $q->getQuery()->execute();
    }
}

