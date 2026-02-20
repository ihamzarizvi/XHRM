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
use XHRM\Entity\OvertimeRule;
use XHRM\Payroll\Api\Model\OvertimeRuleModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class OvertimeRuleAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $rule = new OvertimeRule();
        $this->setOvertimeRule($rule);
        $this->getPayrollService()->getPayrollDao()->saveOvertimeRule($rule);
        return new EndpointResourceResult(OvertimeRuleModel::class, $rule);
    }

    public function getAll(): EndpointResult
    {
        $rules = $this->getPayrollService()->getPayrollDao()->getOvertimeRuleList();
        return new EndpointCollectionResult(
            OvertimeRuleModel::class,
            $rules,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($rules)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $rule = $this->getPayrollService()->getPayrollDao()->getOvertimeRuleById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($rule, OvertimeRule::class);
        return new EndpointResourceResult(OvertimeRuleModel::class, $rule);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $rule = $this->getPayrollService()->getPayrollDao()->getOvertimeRuleById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($rule, OvertimeRule::class);
        $this->setOvertimeRule($rule);
        $this->getPayrollService()->getPayrollDao()->saveOvertimeRule($rule);
        return new EndpointResourceResult(OvertimeRuleModel::class, $rule);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteOvertimeRules($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setOvertimeRule(OvertimeRule $rule): void
    {
        $rule->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'name'));
        $rule->setType($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'type'));
        $rule->setRateMultiplier((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'rateMultiplier'));
        $rule->setMaxOtHoursPerDay((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'maxOtHoursPerDay'));
        $rule->setIsActive($this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, 'isActive', true));
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('name', new Rule(Rules::STRING_TYPE)),
            new ParamRule('type', new Rule(Rules::STRING_TYPE), new Rule(Rules::IN, [['weekday', 'weekend', 'holiday']])),
            new ParamRule('rateMultiplier', new Rule(Rules::NUMBER)),
            new ParamRule('maxOtHoursPerDay', new Rule(Rules::NUMBER)),
            new ParamRule('isActive', new Rule(Rules::BOOL_VAL)),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(...$this->getSortingAndPaginationParamsRules([]));
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
