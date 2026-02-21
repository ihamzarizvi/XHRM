<?php

namespace XHRM\Payroll\Api;

use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\EndpointResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Payroll\Service\PayslipEmailService;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;

class PayrollEmailAPI extends Endpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;

    /**
     * Send payslip emails for a payroll run
     */
    public function getAll(): EndpointResult
    {
        // Not used
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    /**
     * Trigger email sending for an approved payroll run
     */
    public function create(): EndpointResult
    {
        $runId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, 'runId');

        $emailService = new PayslipEmailService();
        $results = $emailService->emailPayslipsForRun($runId);

        return new EndpointResourceResult(ArrayModel::class, $results);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('runId', new Rule(Rules::POSITIVE))
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection();
        $rules->setStrict(false);
        return $rules;
    }
}
