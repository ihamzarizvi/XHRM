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

namespace XHRM\Leave\Report;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\Rest\ReportAPI;
use XHRM\Core\Api\V2\Exception\BadRequestException;
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
use XHRM\Core\Report\Header\Column;
use XHRM\Core\Report\Header\Header;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\Service\TextHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\I18N\Traits\Service\I18NHelperTrait;
use XHRM\Leave\Api\LeaveCommonParams;
use XHRM\Leave\Dto\EmployeeLeaveEntitlementUsageReportSearchFilterParams;
use XHRM\Leave\Traits\Service\LeaveConfigServiceTrait;
use XHRM\Leave\Traits\Service\LeavePeriodServiceTrait;

class EmployeeLeaveEntitlementUsageReport implements EndpointAwareReport
{
    use AuthUserTrait;
    use LeavePeriodServiceTrait;
    use TextHelperTrait;
    use UserRoleManagerTrait;
    use I18NHelperTrait;
    use LeaveConfigServiceTrait;

    public const PARAMETER_LEAVE_TYPE_NAME = 'leaveTypeName';
    public const PARAMETER_ENTITLEMENT_DAYS = 'entitlementDays';
    public const PARAMETER_PENDING_APPROVAL_DAYS = 'pendingApprovalDays';
    public const PARAMETER_SCHEDULED_DAYS = 'scheduledDays';
    public const PARAMETER_TAKEN_DAYS = 'takenDays';
    public const PARAMETER_BALANCE_DAYS = 'balanceDays';

    public const DEFAULT_COLUMN_SIZE = 150;

    /**
     * @return Header
     */
    public function getHeaderDefinition(): Header
    {
        return new Header(
            [
                (new Column(self::PARAMETER_LEAVE_TYPE_NAME))
                    ->setName($this->getI18NHelper()->transBySource('Leave Type'))
                    ->setPin(Column::PIN_COL_START)
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_ENTITLEMENT_DAYS))
                    ->setName($this->getI18NHelper()->transBySource('Leave Entitlements (Days)'))
                    ->setCellProperties(['class' => ['col-alt' => true, 'cell-action' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_PENDING_APPROVAL_DAYS))
                    ->setName($this->getI18NHelper()->transBySource('Leave Pending Approval (Days)'))
                    ->setCellProperties(['class' => ['cell-action' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_SCHEDULED_DAYS))
                    ->setName($this->getI18NHelper()->transBySource('Leave Scheduled (Days)'))
                    ->setCellProperties(['class' => ['cell-action' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_TAKEN_DAYS))
                    ->setName($this->getI18NHelper()->transBySource('Leave Taken (Days)'))
                    ->setCellProperties(['class' => ['cell-action' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
                (new Column(self::PARAMETER_BALANCE_DAYS))
                    ->setName($this->getI18NHelper()->transBySource('Leave Balance (Days)'))
                    ->setCellProperties(['class' => ['col-alt' => true]])
                    ->setSize(self::DEFAULT_COLUMN_SIZE),
            ]
        );
    }

    /**
     * @return Filter
     */
    public function getFilterDefinition(): Filter
    {
        return new Filter();
    }

    /**
     * @param EmployeeLeaveEntitlementUsageReportSearchFilterParams $filterParams
     * @return EmployeeLeaveEntitlementUsageReportData
     */
    public function getData(FilterParams $filterParams): EmployeeLeaveEntitlementUsageReportData
    {
        return new EmployeeLeaveEntitlementUsageReportData($filterParams);
    }

    /**
     * @inheritDoc
     */
    public function prepareFilterParams(EndpointProxy $endpoint): FilterParams
    {
        $filterParams = new EmployeeLeaveEntitlementUsageReportSearchFilterParams();
        $filterParams->setEmpNumber(
            $endpoint->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_QUERY,
                CommonParams::PARAMETER_EMP_NUMBER,
                $this->getAuthUser()->getEmpNumber()
            )
        );
        $endpoint->setSortingAndPaginationParams($filterParams);
        $leavePeriod = $this->getLeavePeriodService()->getCurrentLeavePeriod();
        $filterParams->setFromDate(
            $endpoint->getRequestParams()->getDateTime(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_FROM_DATE,
                null,
                $leavePeriod->getStartDate()
            )
        );
        $filterParams->setToDate(
            $endpoint->getRequestParams()->getDateTime(
                RequestParams::PARAM_TYPE_QUERY,
                LeaveCommonParams::PARAMETER_TO_DATE,
                null,
                $leavePeriod->getEndDate()
            )
        );
        $reportName = $endpoint->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            ReportAPI::PARAMETER_NAME
        );
        if (
            $this->getTextHelper()->strStartsWith(
                $reportName,
                EmployeeLeaveEntitlementUsageReportSearchFilterParams::REPORT_TYPE_MY
            )
        ) {
            $filterParams->setReportType(EmployeeLeaveEntitlementUsageReportSearchFilterParams::REPORT_TYPE_MY);
        }
        return $filterParams;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRule(EndpointProxy $endpoint): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $endpoint->getValidationDecorator()->requiredParamRule(
                new ParamRule(CommonParams::PARAMETER_EMP_NUMBER, new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS))
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(LeaveCommonParams::PARAMETER_FROM_DATE, new Rule(Rules::API_DATE))
            ),
            $endpoint->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(LeaveCommonParams::PARAMETER_TO_DATE, new Rule(Rules::API_DATE))
            ),
            ...
            $endpoint->getSortingAndPaginationParamsRules(
                EmployeeLeaveEntitlementUsageReportSearchFilterParams::ALLOWED_SORT_FIELDS
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function checkReportAccessibility(EndpointProxy $endpoint): void
    {
        $dataGroup = 'leave_report_employee_leave_entitlements_and_usage';
        $reportName = $endpoint->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            ReportAPI::PARAMETER_NAME
        );
        if (
            $this->getTextHelper()->strStartsWith(
                $reportName,
                EmployeeLeaveEntitlementUsageReportSearchFilterParams::REPORT_TYPE_MY
            )
        ) {
            $dataGroup = 'leave_report_my_leave_entitlements_and_usage';
        }
        if (!$this->getUserRoleManagerHelper()->getEntityIndependentDataGroupPermissions($dataGroup)->canRead()) {
            throw new ForbiddenException();
        }
        if (!$this->getLeaveConfigService()->isLeavePeriodDefined()) {
            throw new BadRequestException("Leave period is not defined");
        }
    }
}
