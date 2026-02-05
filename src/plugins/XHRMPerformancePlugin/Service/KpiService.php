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

namespace XHRM\Performance\Service;

use Exception;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Kpi;
use XHRM\ORM\Exception\TransactionException;
use XHRM\Performance\Dao\KpiDao;
use XHRM\Performance\Exception\KpiServiceException;

class KpiService
{
    use EntityManagerHelperTrait;

    private ?KpiDao $kpiDao = null;

    /**
     * @return KpiDao
     */
    public function getKpiDao(): KpiDao
    {
        if (!($this->kpiDao instanceof KpiDao)) {
            $this->kpiDao = new KpiDao();
        }
        return $this->kpiDao;
    }

    /**
     * @param Kpi $kpi
     * @return Kpi
     * @throws KpiServiceException|TransactionException
     */
    public function saveKpi(Kpi $kpi): Kpi
    {
        if ($kpi->getMinRating() >= $kpi->getMaxRating()) {
            throw KpiServiceException::minGreaterThanMax();
        }
        $this->beginTransaction();
        try {
            $kpi = $this->getKpiDao()->saveKpi($kpi);
            if ($kpi->isDefaultKpi()) {
                $this->getKpiDao()->unsetDefaultKpi($kpi->getId());
            }
            $this->commitTransaction();
            return $kpi;
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }
    }
}

