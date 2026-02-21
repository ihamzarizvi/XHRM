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
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\FinancialYear;
use XHRM\Payroll\Api\Model\FinancialYearModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class FinancialYearAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $fy = new FinancialYear();
        $this->setFinancialYear($fy);
        $this->getPayrollService()->getPayrollDao()->saveFinancialYear($fy);
        return new EndpointResourceResult(FinancialYearModel::class, $fy);
    }

    public function getAll(): EndpointResult
    {
        $years = $this->getPayrollService()->getPayrollDao()->getFinancialYearList();
        return new EndpointCollectionResult(
            FinancialYearModel::class,
            $years,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($years)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $fy = $this->getPayrollService()->getPayrollDao()->getFinancialYearById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($fy, FinancialYear::class);
        return new EndpointResourceResult(FinancialYearModel::class, $fy);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $fy = $this->getPayrollService()->getPayrollDao()->getFinancialYearById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($fy, FinancialYear::class);
        $this->setFinancialYear($fy);
        $this->getPayrollService()->getPayrollDao()->saveFinancialYear($fy);
        return new EndpointResourceResult(FinancialYearModel::class, $fy);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteFinancialYears($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setFinancialYear(FinancialYear $fy): void
    {
        $fy->setLabel($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'label'));
        $fy->setStartDate(new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'startDate')));
        $fy->setEndDate(new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'endDate')));
        $fy->setStatus($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'status') ?? 'active');
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('label', new Rule(Rules::STRING_TYPE), new Rule(Rules::LENGTH, [null, 20])),
            new ParamRule('startDate', new Rule(Rules::DATE)),
            new ParamRule('endDate', new Rule(Rules::DATE)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('status', new Rule(Rules::STRING_TYPE))),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(...$this->getSortingAndPaginationParamsRules([]));
        $rules->setStrict(false);
        return $rules;
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
