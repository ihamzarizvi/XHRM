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

namespace XHRM\Time\Report;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Report\ReportData;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Core\Traits\Service\NumberHelperTrait;
use XHRM\I18N\Traits\Service\I18NHelperTrait;
use XHRM\Time\Api\Model\ProjectActivityModel;
use XHRM\Time\Dto\ProjectActivityDetailedReportSearchFilterParams;
use XHRM\Time\Traits\Service\ProjectServiceTrait;

class ProjectActivityDetailedReportData implements ReportData
{
    use ProjectServiceTrait;
    use NumberHelperTrait;
    use DateTimeHelperTrait;
    use NormalizerServiceTrait;
    use I18NHelperTrait;

    /**
     * @var ProjectActivityDetailedReportSearchFilterParams
     */
    private ProjectActivityDetailedReportSearchFilterParams $filterParams;

    public function __construct(ProjectActivityDetailedReportSearchFilterParams $filterParams)
    {
        $this->filterParams = $filterParams;
    }

    /**
     * @inheritDoc
     */
    public function normalize(): array
    {
        $employees = $this->getProjectService()
            ->getProjectDao()
            ->getProjectActivityDetailedReportCriteriaList($this->filterParams);
        $result = [];
        foreach ($employees as $employee) {
            $termination = $employee['terminationId'];
            $result[] = [
                ProjectActivityReport::PARAMETER_EMPLOYEE_NAME => $termination === null ? $employee['fullName'] : $employee['fullName'] . ' ' . $this->getI18NHelper()->transBySource('(Past Employee)'),
                ProjectReport::PARAMETER_TIME => $this->getNumberHelper()
                    ->numberFormat((float) $employee['totalDuration'] / 3600, 2),
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): ?ParameterBag
    {
        $projectActivity = $this->getProjectService()
            ->getProjectActivityDao()
            ->getProjectActivityByProjectIdAndProjectActivityId(
                $this->filterParams->getProjectId(),
                $this->filterParams->getProjectActivityId()
            );

        $total = $this->getProjectService()
            ->getProjectDao()
            ->getTotalDurationForProjectActivityDetailedReport($this->filterParams);

        return new ParameterBag(
            [
                CommonParams::PARAMETER_TOTAL => $this->getProjectService()
                    ->getProjectDao()
                    ->getProjectReportActivityDetailedCriteriaListCount($this->filterParams),
                'sum' => [
                    'hours' => floor($total / 3600),
                    'minutes' => ($total / 60) % 60,
                    'label' => $this->getNumberHelper()->numberFormat($total / 3600, 2),
                ],
                'projectActivity' => $this->getNormalizerService()
                    ->normalize(ProjectActivityModel::class, $projectActivity),
            ]
        );
    }
}

