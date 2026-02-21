<?php

namespace XHRM\Payroll\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Payslip;
use XHRM\Payroll\Api\Model\PayslipModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class PayslipAPI extends Endpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    /**
     * Get payslips for a payroll run (or all payslips)
     */
    public function getAll(): EndpointResult
    {
        $runId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_ATTRIBUTE, 'runId');
        $payslips = $this->getPayrollService()->getPayrollDao()->getPayslipList($runId);
        return new EndpointCollectionResult(
            PayslipModel::class,
            $payslips,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($payslips)])
        );
    }

    /**
     * Get a single payslip with line items
     */
    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, 'id');
        $payslip = $this->getPayrollService()->getPayrollDao()->getPayslipById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($payslip, Payslip::class);

        // Get line items
        $items = $this->getPayrollService()->getPayrollDao()->getPayslipItems($id);

        return new EndpointResourceResult(
            PayslipModel::class,
            $payslip,
            new ParameterBag([
                'items' => array_map(function ($item) {
                    return [
                        'id' => $item->getId(),
                        'name' => $item->getName(),
                        'type' => $item->getType(),
                        'amount' => $item->getAmount(),
                    ];
                }, $items)
            ])
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('runId', new Rule(Rules::POSITIVE))),
            ...$this->getSortingAndPaginationParamsRules([])
        );
        $rules->setStrict(false);
        return $rules;
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('id', new Rule(Rules::POSITIVE))
        );
    }
}
