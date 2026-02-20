<?php

namespace XHRM\Payroll\Api;

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
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\EmployeeLoan;
use XHRM\Payroll\Api\Model\EmployeeLoanModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;
use DateTime;

class EmployeeLoanAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $loan = new EmployeeLoan();
        $this->setLoan($loan);
        $this->getPayrollService()->getPayrollDao()->saveLoan($loan);
        return new EndpointResourceResult(EmployeeLoanModel::class, $loan);
    }

    public function getAll(): EndpointResult
    {
        $status = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, 'status');
        $loans = $this->getPayrollService()->getPayrollDao()->getLoanList($status);
        return new EndpointCollectionResult(
            EmployeeLoanModel::class,
            $loans,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($loans)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $loan = $this->getPayrollService()->getPayrollDao()->getLoanById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($loan, EmployeeLoan::class);
        return new EndpointResourceResult(EmployeeLoanModel::class, $loan);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $loan = $this->getPayrollService()->getPayrollDao()->getLoanById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($loan, EmployeeLoan::class);
        $this->setLoan($loan);
        $this->getPayrollService()->getPayrollDao()->saveLoan($loan);
        return new EndpointResourceResult(EmployeeLoanModel::class, $loan);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteLoans($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setLoan(EmployeeLoan $loan): void
    {
        $empNumber = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, 'empNumber');
        $loan->getDecorator()->setEmployeeByEmpNumber($empNumber);
        $loan->setLoanType($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'loanType'));
        $loan->setDescription($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, 'description'));
        $loan->setTotalAmount((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'totalAmount'));
        $loan->setMonthlyDeduction((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'monthlyDeduction'));
        $loan->setRemainingAmount((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'remainingAmount'));
        $loan->setStartDate(new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'startDate')));
        $loan->setStatus($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'status') ?? 'active');
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('empNumber', new Rule(Rules::POSITIVE)),
            new ParamRule('loanType', new Rule(Rules::STRING_TYPE), new Rule(Rules::IN, [['advance', 'loan']])),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('description', new Rule(Rules::STRING_TYPE)), true),
            new ParamRule('totalAmount', new Rule(Rules::NUMBER)),
            new ParamRule('monthlyDeduction', new Rule(Rules::NUMBER)),
            new ParamRule('remainingAmount', new Rule(Rules::NUMBER)),
            new ParamRule('startDate', new Rule(Rules::DATE)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('status', new Rule(Rules::STRING_TYPE))),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('status', new Rule(Rules::STRING_TYPE))),
            ...$this->getSortingAndPaginationParamsRules([])
        );
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)));
    }
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)), ...$this->getValidationRuleForCreate()->getIterator());
    }
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_IDS, new Rule(Rules::INT_ARRAY)));
    }
}
