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
use XHRM\Entity\Holiday;
use XHRM\Payroll\Api\Model\HolidayModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class HolidayAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $holiday = new Holiday();
        $this->setHoliday($holiday);
        $this->getPayrollService()->getPayrollDao()->saveHoliday($holiday);
        return new EndpointResourceResult(HolidayModel::class, $holiday);
    }

    public function getAll(): EndpointResult
    {
        $year = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, 'year');
        $holidays = $this->getPayrollService()->getPayrollDao()->getHolidayList($year);
        return new EndpointCollectionResult(
            HolidayModel::class,
            $holidays,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($holidays)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $holiday = $this->getPayrollService()->getPayrollDao()->getHolidayById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($holiday, Holiday::class);
        return new EndpointResourceResult(HolidayModel::class, $holiday);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $holiday = $this->getPayrollService()->getPayrollDao()->getHolidayById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($holiday, Holiday::class);
        $this->setHoliday($holiday);
        $this->getPayrollService()->getPayrollDao()->saveHoliday($holiday);
        return new EndpointResourceResult(HolidayModel::class, $holiday);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteHolidays($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setHoliday(Holiday $holiday): void
    {
        $holiday->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'name'));
        $holiday->setDate(new DateTime($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'date')));
        $holiday->setIsRecurring($this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, 'isRecurring', false));
        $holiday->setIsHalfDay($this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, 'isHalfDay', false));
        $holiday->setAppliesTo($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'appliesTo') ?? 'all');
        $holiday->setDepartmentId($this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, 'departmentId'));
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('name', new Rule(Rules::STRING_TYPE), new Rule(Rules::LENGTH, [null, 100])),
            new ParamRule('date', new Rule(Rules::DATE)),
            new ParamRule('isRecurring', new Rule(Rules::BOOL_VAL)),
            new ParamRule('isHalfDay', new Rule(Rules::BOOL_VAL)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('appliesTo', new Rule(Rules::STRING_TYPE))),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('departmentId', new Rule(Rules::POSITIVE)), true),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('year', new Rule(Rules::POSITIVE))),
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
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE)), ...$this->getValidationRuleForCreate()->getIterator());
    }
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(new ParamRule(self::PARAMETER_IDS, new Rule(Rules::INT_ARRAY)));
    }
}
