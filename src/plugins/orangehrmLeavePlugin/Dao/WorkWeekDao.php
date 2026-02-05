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

namespace XHRM\Leave\Dao;

use DateTime;
use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\WorkWeek;

class WorkWeekDao extends BaseDao
{
    /**
     * @param WorkWeek $workWeek
     * @return WorkWeek
     */
    public function saveWorkWeek(WorkWeek $workWeek): WorkWeek
    {
        $this->persist($workWeek);
        return $workWeek;
    }

    /**
     * @param int $id
     * @return WorkWeek|null
     */
    public function getWorkWeekById(int $id): ?WorkWeek
    {
        return $this->getRepository(WorkWeek::class)->find($id);
    }

    /**
     * @param DateTime $date
     * @param bool $isFullDay
     * @return bool
     */
    public function isNonWorkingDay(DateTime $date, bool $isFullDay = true): bool
    {
        $q = $this->createQueryBuilder(WorkWeek::class, 'workWeek');
        /** @var WorkWeek $workWeek */
        $workWeek = $this->fetchOne($q);

        $getter = 'get' . $date->format('l');
        if ($isFullDay) {
            return ($workWeek->$getter() == WorkWeek::WORKWEEK_LENGTH_NON_WORKING_DAY);
        } else {
            return ($workWeek->$getter() == WorkWeek::WORKWEEK_LENGTH_HALF_DAY);
        }
    }
}
