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

namespace XHRM\Core\Dao;

class ValidationUniqueDao extends BaseDao
{
    /**
     * @param string $value
     * @param string $entityName
     * @param string $attributeName
     * @param string|null $entityId
     * @param string|null $matchByField
     * @param string|null $matchByValue
     * @return bool
     */
    public function isValueUnique(string $value, string $entityName, string $attributeName, ?string $entityId, ?string $matchByField, ?string $matchByValue): bool
    {
        $qb = $this->createQueryBuilder('XHRM\\Entity\\' . $entityName, 'entity');
        $qb->andWhere($qb->expr()->eq('entity.' . $attributeName, ':value'))
            ->setParameter('value', $value);

        if (!is_null($entityId)) {
            $qb->andWhere($qb->expr()->neq('entity.id', ':id'))
                ->setParameter('id', $entityId);
        }

        if (!is_null($matchByValue) && !is_null($matchByField)) {
            $qb->andWhere($qb->expr()->eq('entity.' . $matchByField, ':matchValue'))
                ->setParameter('matchValue', $matchByValue);
        }

        return $this->count($qb) == 0;
    }
}

