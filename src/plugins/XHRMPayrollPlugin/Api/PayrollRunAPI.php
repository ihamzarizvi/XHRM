<?php

namespace XHRM\Payroll\Api;

use DateTime;
use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CrudEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\PayrollRun;
use XHRM\Payroll\Api\Model\PayrollRunModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class PayrollRunAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;
    use AuthUserTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    /**
     * Generate a new payroll run
     */
    public function create(): EndpointResult
    {
        $run = new PayrollRun();
        $run->setPeriodType(
            $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'periodType')
        );
        $run->setPeriodStart(
            new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'periodStart'))
        );
        $run->setPeriodEnd(
            new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'periodEnd'))
        );
        $run->setCurrencyId(
            $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'currencyId') ?? 'PKR'
        );
        $run->setStatus('draft');
        $run->setGeneratedAt(new DateTime());

        $empNumber = $this->getAuthUser()->getEmpNumber();
        $run->getDecorator()->setGeneratedByEmpNumber($empNumber);

        // Save run first, then generate payslips
        $this->getPayrollService()->getPayrollDao()->savePayrollRun($run);

        // Generate payslips for all active employees
        $this->getPayrollService()->generatePayslips($run);

        // Reload totals
        $run = $this->getPayrollService()->getPayrollDao()->getPayrollRunById($run->getId());

        return new EndpointResourceResult(PayrollRunModel::class, $run);
    }

    /**
     * List all payroll runs (with optional status filter)
     */
    public function getAll(): EndpointResult
    {
        $status = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, 'status');
        $runs = $this->getPayrollService()->getPayrollDao()->getPayrollRunList($status);
        return new EndpointCollectionResult(
            PayrollRunModel::class,
            $runs,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($runs)])
        );
    }

    /**
     * Get a single payroll run
     */
    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $run = $this->getPayrollService()->getPayrollDao()->getPayrollRunById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($run, PayrollRun::class);
        return new EndpointResourceResult(PayrollRunModel::class, $run);
    }

    /**
     * Update payroll run status (submit for approval)
     */
    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $run = $this->getPayrollService()->getPayrollDao()->getPayrollRunById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($run, PayrollRun::class);

        $newStatus = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, 'status');
        if ($newStatus && in_array($newStatus, ['pending_approval', 'draft'])) {
            $run->setStatus($newStatus);
        }
        $this->getPayrollService()->getPayrollDao()->savePayrollRun($run);

        return new EndpointResourceResult(PayrollRunModel::class, $run);
    }

    /**
     * Delete draft payroll runs
     */
    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deletePayrollRuns($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('periodType', new Rule(Rules::STRING_TYPE), new Rule(Rules::IN, [['monthly', 'biweekly', 'weekly', 'contract', 'hourly']])),
            new ParamRule('periodStart', new Rule(Rules::DATE)),
            new ParamRule('periodEnd', new Rule(Rules::DATE)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('currencyId', new Rule(Rules::STRING_TYPE))),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('status', new Rule(Rules::STRING_TYPE))),
            ...$this->getSortingAndPaginationParamsRules([])
        );
        $rules->setStrict(false);
        return $rules;
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)));
    }

    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('status', new Rule(Rules::STRING_TYPE))),
        );
    }

    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_IDS, new Rule(Rules::INT_ARRAY)));
    }
}
