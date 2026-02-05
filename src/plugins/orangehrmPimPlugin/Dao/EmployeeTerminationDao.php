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

namespace XHRM\Pim\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\EmployeeTerminationRecord;
use XHRM\Entity\TerminationReason;
use XHRM\ORM\ListSorter;

class EmployeeTerminationDao extends BaseDao
{
    /**
     * @param EmployeeTerminationRecord $employeeTerminationRecord
     * @return EmployeeTerminationRecord
     */
    public function saveEmployeeTermination(
        EmployeeTerminationRecord $employeeTerminationRecord
    ): EmployeeTerminationRecord {
        $this->persist($employeeTerminationRecord);
        return $employeeTerminationRecord;
    }

    /**
     * @param int $id
     * @return EmployeeTerminationRecord|null
     */
    public function getEmployeeTermination(int $id): ?EmployeeTerminationRecord
    {
        return $this->getRepository(EmployeeTerminationRecord::class)->find($id);
    }

    /**
     * @return TerminationReason[]
     */
    public function getTerminationReasonList(): array
    {
        $q = $this->createQueryBuilder(TerminationReason::class, 'tr');
        $q->addOrderBy('tr.name', ListSorter::ASCENDING);
        return $q->getQuery()->execute();
    }
}
