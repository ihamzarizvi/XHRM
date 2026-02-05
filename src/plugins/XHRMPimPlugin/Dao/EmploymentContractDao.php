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
use XHRM\Entity\EmpContract;

class EmploymentContractDao extends BaseDao
{
    /**
     * @param int $empNumber
     * @return EmpContract|null
     */
    public function getEmploymentContractByEmpNumber(int $empNumber): ?EmpContract
    {
        $q = $this->createQueryBuilder(EmpContract::class, 'c');
        $q->andWhere('c.employee = :empNumber')
            ->setParameter('empNumber', $empNumber);

        return $this->fetchOne($q);
    }

    /**
     * @param EmpContract $employmentContract
     * @return EmpContract
     */
    public function saveEmploymentContract(EmpContract $employmentContract): EmpContract
    {
        $this->persist($employmentContract);
        return $employmentContract;
    }
}

