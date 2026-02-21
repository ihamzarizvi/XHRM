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
use XHRM\Entity\AttendanceRule;
use XHRM\Payroll\Api\Model\AttendanceRuleModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class AttendanceRuleAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $rule = new AttendanceRule();
        $this->setAttendanceRule($rule);
        $this->getPayrollService()->getPayrollDao()->saveAttendanceRule($rule);
        return new EndpointResourceResult(AttendanceRuleModel::class, $rule);
    }

    public function getAll(): EndpointResult
    {
        $rules = $this->getPayrollService()->getPayrollDao()->getAttendanceRuleList();
        return new EndpointCollectionResult(
            AttendanceRuleModel::class,
            $rules,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($rules)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $rule = $this->getPayrollService()->getPayrollDao()->getAttendanceRuleById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($rule, AttendanceRule::class);
        return new EndpointResourceResult(AttendanceRuleModel::class, $rule);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $rule = $this->getPayrollService()->getPayrollDao()->getAttendanceRuleById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($rule, AttendanceRule::class);
        $this->setAttendanceRule($rule);
        $this->getPayrollService()->getPayrollDao()->saveAttendanceRule($rule);
        return new EndpointResourceResult(AttendanceRuleModel::class, $rule);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteAttendanceRules($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setAttendanceRule(AttendanceRule $rule): void
    {
        $rule->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'name'));
        $rule->setGracePeriodMinutes($this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, 'gracePeriodMinutes'));
        $rule->setHalfDayHours((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'halfDayHours'));
        $rule->setLatesPerAbsent($this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, 'latesPerAbsent'));
        $rule->setIsDefault($this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, 'isDefault', true));
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('name', new Rule(Rules::STRING_TYPE), new Rule(Rules::LENGTH, [null, 100])),
            new ParamRule('gracePeriodMinutes', new Rule(Rules::INT_TYPE)),
            new ParamRule('halfDayHours', new Rule(Rules::NUMBER)),
            new ParamRule('latesPerAbsent', new Rule(Rules::INT_TYPE)),
            new ParamRule('isDefault', new Rule(Rules::BOOL_VAL)),
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
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            ...$this->getValidationRuleForCreate()->getIterator()
        );
    }

    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_IDS, new Rule(Rules::INT_ARRAY)));
    }
}
