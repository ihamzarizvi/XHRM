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
use XHRM\Entity\SalaryComponent;
use XHRM\Payroll\Api\Model\SalaryComponentModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class SalaryComponentAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';
    public const PARAMETER_NAME = 'name';
    public const PARAMETER_CODE = 'code';
    public const PARAMETER_TYPE = 'type';
    public const PARAMETER_CALCULATION_TYPE = 'calculationType';
    public const PARAMETER_DEFAULT_VALUE = 'defaultValue';
    public const PARAMETER_FORMULA = 'formula';
    public const PARAMETER_IS_TAXABLE = 'isTaxable';
    public const PARAMETER_IS_ACTIVE = 'isActive';
    public const PARAMETER_SORT_ORDER = 'sortOrder';
    public const PARAMETER_APPLIES_TO = 'appliesTo';

    public function create(): EndpointResult
    {
        $component = new SalaryComponent();
        $this->setSalaryComponent($component);
        $this->getPayrollService()->getPayrollDao()->saveSalaryComponent($component);
        return new EndpointResourceResult(SalaryComponentModel::class, $component);
    }

    public function getAll(): EndpointResult
    {
        $type = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_TYPE);
        $isActive = $this->getRequestParams()->getBooleanOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_IS_ACTIVE);

        $components = $this->getPayrollService()->getPayrollDao()->getSalaryComponentList($type, $isActive);
        $count = count($components);

        return new EndpointCollectionResult(
            SalaryComponentModel::class,
            $components,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => $count])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $component = $this->getPayrollService()->getPayrollDao()->getSalaryComponentById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($component, SalaryComponent::class);
        return new EndpointResourceResult(SalaryComponentModel::class, $component);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $component = $this->getPayrollService()->getPayrollDao()->getSalaryComponentById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($component, SalaryComponent::class);
        $this->setSalaryComponent($component);
        $this->getPayrollService()->getPayrollDao()->saveSalaryComponent($component);
        return new EndpointResourceResult(SalaryComponentModel::class, $component);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteSalaryComponents($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setSalaryComponent(SalaryComponent $component): void
    {
        $component->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NAME));
        $component->setCode($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_CODE));
        $component->setType($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_TYPE));
        $component->setCalculationType(
            $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_CALCULATION_TYPE) ?? 'fixed'
        );
        $component->setDefaultValue(
            $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_DEFAULT_VALUE)
        );
        $component->setFormula(
            $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_FORMULA)
        );
        $component->setIsTaxable(
            $this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IS_TAXABLE, true)
        );
        $component->setIsActive(
            $this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IS_ACTIVE, true)
        );
        $component->setSortOrder(
            $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SORT_ORDER) ?? 0
        );
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_NAME, new Rule(Rules::STRING_TYPE), new Rule(Rules::LENGTH, [null, 100])),
            new ParamRule(self::PARAMETER_CODE, new Rule(Rules::STRING_TYPE), new Rule(Rules::LENGTH, [null, 20])),
            new ParamRule(self::PARAMETER_TYPE, new Rule(Rules::STRING_TYPE), new Rule(Rules::IN, [['earning', 'deduction']])),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_CALCULATION_TYPE, new Rule(Rules::STRING_TYPE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_DEFAULT_VALUE, new Rule(Rules::STRING_TYPE)),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_FORMULA, new Rule(Rules::STRING_TYPE)),
                true
            ),
            new ParamRule(self::PARAMETER_IS_TAXABLE, new Rule(Rules::BOOL_VAL)),
            new ParamRule(self::PARAMETER_IS_ACTIVE, new Rule(Rules::BOOL_VAL)),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_SORT_ORDER, new Rule(Rules::INT_TYPE))
            ),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_TYPE, new Rule(Rules::STRING_TYPE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_IS_ACTIVE, new Rule(Rules::BOOL_VAL))
            ),
            ...$this->getSortingAndPaginationParamsRules(['salaryComponent.name', 'salaryComponent.sortOrder'])
        );
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE))
        );
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
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_IDS, new Rule(Rules::INT_ARRAY))
        );
    }
}
