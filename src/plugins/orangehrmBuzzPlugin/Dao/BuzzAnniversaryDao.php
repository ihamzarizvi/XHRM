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

namespace XHRM\Buzz\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\Employee;
use XHRM\ORM\Paginator;
use XHRM\Buzz\Dto\EmployeeAnniversarySearchFilterParams;

class BuzzAnniversaryDao extends BaseDao
{
    /**
     * @param EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
     * @return array
     */
    public function getUpcomingAnniversariesList(
        EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
    ): array {
        return $this->getUpcomingAnniversariesPaginator($employeeAnniversarySearchFilterParams)->getQuery()->execute();
    }

    /**
     * @param EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
     * @return Paginator
     */
    private function getUpcomingAnniversariesPaginator(
        EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
    ): Paginator {
        $q = $this->createQueryBuilder(Employee::class, 'employee');
        $this->setSortingAndPaginationParams($q, $employeeAnniversarySearchFilterParams);

        $orExpr = $q->expr()->orX(
            $q->expr()->between(
                'DATE_DIFF(:nextDate, CONCAT(:thisYear, SUBSTRING(employee.joinedDate,5,6)))',
                ':dateDiffMin',
                ':dateDiffMax'
            )
        );

        if (
            ($nextYear = $employeeAnniversarySearchFilterParams->getNextDate()->format('Y'))
            != $employeeAnniversarySearchFilterParams->getThisYear()
        ) {
            $orExpr->add(
                $q->expr()->between(
                    'DATE_DIFF(:nextDate, CONCAT(:nextYear, SUBSTRING(employee.joinedDate,5,6)))',
                    ':dateDiffMin',
                    ':dateDiffMax'
                )
            );
            $q->setParameter('nextYear', $nextYear);
        }

        $q->andWhere($orExpr)
            ->setParameter('thisYear', $employeeAnniversarySearchFilterParams->getThisYear())
            ->setParameter('nextDate', $employeeAnniversarySearchFilterParams->getNextDate())
            ->setParameter('dateDiffMin', $employeeAnniversarySearchFilterParams->getDateDiffMin())
            ->setParameter('dateDiffMax', $employeeAnniversarySearchFilterParams->getDateDiffMax());

        $q->andWhere($q->expr()->neq('SUBSTRING(employee.joinedDate, 1, 4)', ':thisYear'))
            ->setParameter('thisYear', $employeeAnniversarySearchFilterParams->getThisYear());
        $q->andWhere($q->expr()->isNull('employee.employeeTerminationRecord'));
        $q->andWhere($q->expr()->isNull('employee.purgedAt'));

        return $this->getPaginator($q);
    }

    /**
     * @param EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
     * @return int
     */
    public function getUpcomingAnniversariesCount(
        EmployeeAnniversarySearchFilterParams $employeeAnniversarySearchFilterParams
    ): int {
        return $this->getUpcomingAnniversariesPaginator($employeeAnniversarySearchFilterParams)->count();
    }
}
