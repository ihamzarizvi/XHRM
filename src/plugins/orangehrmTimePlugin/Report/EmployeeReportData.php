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

namespace XHRM\Time\Report;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Report\ReportData;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Core\Traits\Service\NumberHelperTrait;
use XHRM\Pim\Api\Model\EmployeeModel;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;
use XHRM\Time\Dto\EmployeeReportsSearchFilterParams;
use XHRM\Time\Traits\Service\TimesheetServiceTrait;

class EmployeeReportData implements ReportData
{
    use TimesheetServiceTrait;
    use NumberHelperTrait;
    use NormalizerServiceTrait;
    use EmployeeServiceTrait;

    private EmployeeReportsSearchFilterParams $filterParams;

    /**
     * @param  EmployeeReportsSearchFilterParams  $filterParams
     */
    public function __construct(EmployeeReportsSearchFilterParams $filterParams)
    {
        $this->filterParams = $filterParams;
    }

    public function normalize(): array
    {
        $timesheetItems = $this->getTimesheetService()
            ->getTimesheetDao()
            ->getTimesheetItemsForEmployeeReport($this->filterParams);
        $result = [];
        foreach ($timesheetItems as $timesheetItem) {
            $projectName = $timesheetItem['customerName']
                . ' - ' . $timesheetItem['projectName'];
            $activityName = $timesheetItem['activityName'];
            $duration = $this->getNumberHelper()->numberFormat($timesheetItem['totalDurationByGroup'] / 3600, 2);
            $result[] = [
                EmployeeReport::PARAMETER_PROJECT_NAME => $projectName,
                EmployeeReport::PARAMETER_ACTIVITY_NAME => $activityName,
                EmployeeReport::PARAMETER_DURATION => $duration
            ];
        }
        return $result;
    }

    public function getMeta(): ?ParameterBag
    {
        $employee = $this->getEmployeeService()
            ->getEmployeeDao()
            ->getEmployeeByEmpNumber($this->filterParams->getEmpNumber());
        $totalDuration = $this->getTimesheetService()
            ->getTimesheetDao()
            ->getTotalDurationForEmployeeReport($this->filterParams);

        return new ParameterBag(
            [
                CommonParams::PARAMETER_TOTAL => $this->getTimesheetService()
                    ->getTimesheetDao()
                    ->getTimesheetItemsCountForEmployeeReport($this->filterParams),
                'sum' => [
                    'hours' => floor($totalDuration / 3600),
                    'minutes' => ($totalDuration / 60) % 60,
                    'label' => $this->getNumberHelper()->numberFormat($totalDuration / 3600, 2),
                ],
                'employee' => $this->getNormalizerService()->normalize(EmployeeModel::class, $employee)
            ]
        );
    }
}
