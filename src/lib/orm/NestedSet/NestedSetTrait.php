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

namespace XHRM\ORM\NestedSet;

use XHRM\ORM\Doctrine;
use XHRM\ORM\ListSorter;

trait NestedSetTrait
{
    /**
     * @param int|null $depth
     * @return NestedSetInterface[]|array
     * @throws NestedSetException
     */
    public static function fetchTree(?int $depth = null): array
    {
        $entityClass = get_called_class();
        if (!(in_array(NestedSetInterface::class, array_values(class_implements($entityClass))))) {
            throw new NestedSetException(
                sprintf(
                    'Expected called class implements `%s`, and got called class as `%s`',
                    NestedSetInterface::class,
                    $entityClass
                )
            );
        }

        $q = Doctrine::getEntityManager()->getRepository($entityClass)->createQueryBuilder('e');
        $q->andWhere('e.lft >= :lft');
        $q->setParameter('lft', 1);
        $q->addOrderBy('e.lft', ListSorter::ASCENDING);
        if (!is_null($depth)) {
            $q->andWhere($q->expr()->between('e.level', ':from', ':to'));
            $q->setParameter('from', 0);
            $q->setParameter('to', $depth);
        }

        return $q->getQuery()->execute();
    }
}

