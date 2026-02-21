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
use XHRM\Entity\TaxSlab;
use XHRM\Entity\FinancialYear;
use XHRM\Payroll\Api\Model\TaxSlabModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class TaxSlabAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';

    public function create(): EndpointResult
    {
        $slab = new TaxSlab();
        $this->setTaxSlab($slab);
        $this->getPayrollService()->getPayrollDao()->saveTaxSlab($slab);
        return new EndpointResourceResult(TaxSlabModel::class, $slab);
    }

    public function getAll(): EndpointResult
    {
        $financialYearId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, 'financialYearId');
        $slabs = $this->getPayrollService()->getPayrollDao()->getTaxSlabList($financialYearId);
        return new EndpointCollectionResult(
            TaxSlabModel::class,
            $slabs,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => count($slabs)])
        );
    }

    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $slab = $this->getPayrollService()->getPayrollDao()->getTaxSlabById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($slab, TaxSlab::class);
        return new EndpointResourceResult(TaxSlabModel::class, $slab);
    }

    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $slab = $this->getPayrollService()->getPayrollDao()->getTaxSlabById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($slab, TaxSlab::class);
        $this->setTaxSlab($slab);
        $this->getPayrollService()->getPayrollDao()->saveTaxSlab($slab);
        return new EndpointResourceResult(TaxSlabModel::class, $slab);
    }

    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getPayrollService()->getPayrollDao()->deleteTaxSlabs($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    private function setTaxSlab(TaxSlab $slab): void
    {
        $fyId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, 'financialYearId');
        $fy = $this->getPayrollService()->getPayrollDao()->getFinancialYearById($fyId);
        $slab->setFinancialYear($fy);
        $slab->setMinIncome((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'minIncome'));
        $maxIncome = $this->getRequestParams()->getFloatOrNull(RequestParams::PARAM_TYPE_BODY, 'maxIncome');
        $slab->setMaxIncome($maxIncome !== null ? (string) $maxIncome : null);
        $slab->setTaxRate((string) $this->getRequestParams()->getFloat(RequestParams::PARAM_TYPE_BODY, 'taxRate'));
        $slab->setFixedAmount((string) ($this->getRequestParams()->getFloatOrNull(RequestParams::PARAM_TYPE_BODY, 'fixedAmount') ?? 0));
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('financialYearId', new Rule(Rules::POSITIVE)),
            new ParamRule('minIncome', new Rule(Rules::NUMBER)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('maxIncome', new Rule(Rules::NUMBER)), true),
            new ParamRule('taxRate', new Rule(Rules::NUMBER)),
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('fixedAmount', new Rule(Rules::NUMBER))),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(new ParamRule('financialYearId', new Rule(Rules::POSITIVE))),
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
