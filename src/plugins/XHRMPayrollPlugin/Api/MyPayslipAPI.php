<?php

namespace XHRM\Payroll\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Payroll\Api\Model\PayslipModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class MyPayslipAPI extends Endpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;
    use AuthUserTrait;

    /**
     * Get payslips for current logged-in employee
     */
    public function getAll(): EndpointResult
    {
        $empNumber = $this->getAuthUser()->getEmpNumber();
        $payslips = $this->getPayrollService()->getPayrollDao()->getPayslipsByEmployee($empNumber);
        return new EndpointCollectionResult(
            PayslipModel::class,
            $payslips,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($payslips)])
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            ...$this->getSortingAndPaginationParamsRules([])
        );
    }
}
