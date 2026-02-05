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

namespace XHRM\Pim\Report;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Report\ReportData;
use XHRM\Core\Service\ReportGeneratorService;
use XHRM\Pim\Dto\PimReportSearchFilterParams;

class PimReportData implements ReportData
{
    /**
     * @var ReportGeneratorService|null
     */
    private ?ReportGeneratorService $reportGeneratorService = null;

    private PimReportSearchFilterParams $filterParams;

    public function __construct(PimReportSearchFilterParams $filterParams)
    {
        $this->filterParams = $filterParams;
    }

    /**
     * @return ReportGeneratorService
     */
    protected function getReportGeneratorService(): ReportGeneratorService
    {
        if (!$this->reportGeneratorService instanceof ReportGeneratorService) {
            $this->reportGeneratorService = new ReportGeneratorService();
        }
        return $this->reportGeneratorService;
    }

    /**
     * @inheritDoc
     */
    public function normalize(): array
    {
        return $this->getReportGeneratorService()->getNormalizedReportData($this->filterParams);
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): ?ParameterBag
    {
        $count = $this->getReportGeneratorService()->getReportDataCount($this->filterParams);
        return new ParameterBag([CommonParams::PARAMETER_TOTAL => $count]);
    }
}

