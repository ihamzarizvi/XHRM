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

namespace XHRM\Pim\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\EmployeeEvent;
use XHRM\ORM\Paginator;
use XHRM\Pim\Dto\EmployeeEventSearchFilterParams;

class EmployeeEventDao extends BaseDao
{
    /**
     * Save employee event
     *
     * @param EmployeeEvent $employeeEvent
     * @return EmployeeEvent
     */
    public function saveEmployeeEvent(EmployeeEvent $employeeEvent): EmployeeEvent
    {
        $this->persist($employeeEvent);
        return $employeeEvent;
    }

    /**
     * Get employee event
     *
     * @param EmployeeEventSearchFilterParams $employeeEventSearchFilterParams
     * @return EmployeeEvent[]
     */
    public function getEmployeeEvents(EmployeeEventSearchFilterParams $employeeEventSearchFilterParams): array
    {
        $paginator = $this->getEmployeeEventPaginator($employeeEventSearchFilterParams);
        return $paginator->getQuery()->execute();
    }

    /**
     * @param EmployeeEventSearchFilterParams $employeeEventSearchFilterParams
     * @return Paginator
     */
    private function getEmployeeEventPaginator(
        EmployeeEventSearchFilterParams $employeeEventSearchFilterParams
    ): Paginator {
        $q = $this->createQueryBuilder(EmployeeEvent::class, 'event');
        $this->setSortingAndPaginationParams($q, $employeeEventSearchFilterParams);

        if ($employeeEventSearchFilterParams->getDateRange()) {
            if ($employeeEventSearchFilterParams->getDateRange()->getFromDate()) {
                $q->andWhere('event.createdDate >= :fromDate')
                    ->setParameter('fromDate', $employeeEventSearchFilterParams->getDateRange()->getFromDate());
            }
            if ($employeeEventSearchFilterParams->getDateRange()->getToDate()) {
                $q->andWhere('event.createdDate <= :toDate')
                    ->setParameter('toDate', $employeeEventSearchFilterParams->getDateRange()->getToDate());
            }
        }
        if ($employeeEventSearchFilterParams->getEmpNumber()) {
            $q->andWhere("event.empNumber = :empNumber")
                ->setParameter('empNumber', $employeeEventSearchFilterParams->getEmpNumber());
        }
        if ($employeeEventSearchFilterParams->getEvent()) {
            $q->andWhere("event.event = :event")
                ->setParameter('event', $employeeEventSearchFilterParams->getEvent());
        }
        if ($employeeEventSearchFilterParams->getType()) {
            $q->andWhere("event.type = :type")
                ->setParameter('type', $employeeEventSearchFilterParams->getType());
        }

        return $this->getPaginator($q);
    }
}

