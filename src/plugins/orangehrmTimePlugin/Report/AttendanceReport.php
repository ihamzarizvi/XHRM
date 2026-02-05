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

use DateTime;
use XHRM\Core\Api\V2\Exception\ForbiddenException;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Dto\FilterParams;
use XHRM\Core\Report\Api\EndpointAwareReport;
use XHRM\Core\Report\Api\EndpointProxy;
use XHRM\Core\Report\Filter\Filter;
use XHRM\Core\Report\Filter\FilterDefinition;
use XHRM\Core\Report\Header\Column;
use XHRM\Core\Report\Header\Header;
use XHRM\Core\Report\Header\HeaderDefinition;
use XHRM\Core\Report\ReportData;
use XHRM\Core\Traits\Service\TextHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\EmploymentStatus;
use XHRM\Entity\JobTitle;
use XHRM\Entity\Subunit;
use XHRM\I18N\Traits\Service\I18NHelperTrait;
use XHRM\Time\Dto\AttendanceReportSearchFilterParams;

class AttendanceReport implements EndpointAwareReport
{
    use UserRoleManagerTrait;
    use TextHelperTrait;
    use I18NHelperTrait;

    public const PARAMETER_EMPLOYEE_NAME = 'employeeName';
    public const PARAMETER_TIME = 'time';

    public const FILTER_EMP_NUMBER = 'empNumber';
    public const FILTER_JOB_TITLE_ID = 'jobTitleId';
    public const FILTER_SUBUNIT_ID = 'subunitId';
    public const FILTER_EMPLOYMENT_STATUS_ID = 'employmentStatusId';
    public const FILTER_PARAMETER_DATE_FROM = 'fromDate';
    public const FILTER_PARAMETER_DATE_TO = 'toDate';


    public const DEFAULT_COLUMN_SIZE = 150;

    /**
     * @inheritDoc
     */
    public function prepareFilterParams(EndpointProxy $endpoint): FilterParams
    {
        $filterParams = new AttendanceReportSearchFilterParams();

        $endpoint->setSortingAndPaginationParams($filterParams);

        $empNumber = $endpoint->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_EMP_NUMBER
        );

        if (!is_null($empNumber)) {
            $filterParams->setEmployeeNumbers([$empNumber]);
        } else {
            $accessibleEmpNumbers = $this->getUserRoleManager()->getAccessibleEntityIds(Employee::class);
            $filterParams->setEmployeeNumbers($accessibleEmpNumbers);
        }

        $filterParams->setJobTitleId(
            $endpoint->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_JOB_TITLE_ID
            )
        );
        $filterParams->setSubunitId(
            $endpoint->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_SUBUNIT_ID
            )
        );
        $filterParams->setEmploymentStatusId(
            $endpoint->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_EMPLOYMENT_STATUS_ID
            )
        );

        $filterParams->setEmploymentStatusId(
            $endpoint->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_EMPLOYMENT_STATUS_ID
            )
        );

        $fromDate = $endpoint->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_PARAMETER_DATE_FROM
        );

        $toDate = $endpoint->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_PARAMETER_DATE_TO
        );

        $filterParams->setFromDate($fromDate ? new DateTime($fromDate . ' ' . '00:00:00') : null);
        $filterParams->setToDate($toDate ? new DateTime($toDate . ' ' . '23:59:59') : null);

        return $filterParams;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRule(EndpointProxy $endpoint): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_EMP_NUMBER,
                    new Rule(Rules::ENTITY_ID_EXISTS, [Employee::class]),
                    new Rule(
                        Rules::IN_ACCESSIBLE_EMP_NUMBERS
                    ) // this is for restrict the supervisor access when supervisor trying to access own record
                )
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_JOB_TITLE_ID,
                    new Rule(Rules::ENTITY_ID_EXISTS, [JobTitle::class])
                )
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_SUBUNIT_ID,
                    new Rule(Rules::ENTITY_ID_EXISTS, [Subunit::class])
                )
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_EMPLOYMENT_STATUS_ID,
                    new Rule(Rules::ENTITY_ID_EXISTS, [EmploymentStatus::class])
                )
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::FILTER_PARAMETER_DATE_FROM, new Rule(Rules::API_DATE))
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_PARAMETER_DATE_TO,
                    new Rule(Rules::API_DATE),
                    new Rule(Rules::CALLBACK, [
                        function () use ($endpoint) {
                            $fromDate = $endpoint->getRequestParams()->getDateTimeOrNull(
                                RequestParams::PARAM_TYPE_QUERY,
                                self::FILTER_PARAMETER_DATE_FROM
                            );

                            $toDate = $endpoint->getRequestParams()->getDateTimeOrNull(
                                RequestParams::PARAM_TYPE_QUERY,
                                self::FILTER_PARAMETER_DATE_TO
                            );

                            if (!is_null($fromDate) && !is_null($toDate)) {
                                if ($fromDate > $toDate) {
                                    return false;
                                }
                            }
                            return true;
                        }
                    ])
                )
            ),
            ...$endpoint->getSortingAndPaginationParamsRules(
                AttendanceReportSearchFilterParams::ALLOWED_SORT_FIELDS
            )
        );
    }

    /**
     * @return Header
     */
    public function getHeaderDefinition(): HeaderDefinition
    {
        return new Header(
            [
                (new Column(self::PARAMETER_EMPLOYEE_NAME))->setName($this->getI18NHelper()->transBySource('Employee Name'))
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_TIME))->setName($this->getI18NHelper()->transBySource('Time (Hours)'))
                    ->setCellProperties(['class' => ['col-alt' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
            ]
        );
    }

    /**
     * @return Filter
     */
    public function getFilterDefinition(): FilterDefinition
    {
        return new Filter();
    }

    /**
     * @param AttendanceReportSearchFilterParams $filterParams
     * @return AttendanceReportData
     */
    public function getData(FilterParams $filterParams): ReportData
    {
        return new AttendanceReportData($filterParams);
    }

    /**
     * @inheritDoc
     */
    public function checkReportAccessibility(EndpointProxy $endpoint): void
    {
        $employeeNumber = $endpoint->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_EMP_NUMBER
        );
        if (
            !$this->getUserRoleManager()->getDataGroupPermissions(
                'attendance_summary',
                [],
                [],
                $this->getUserRoleManagerHelper()->isSelfByEmpNumber($employeeNumber)
            )->canRead()
        ) {
            throw new ForbiddenException();
        }
    }
}
