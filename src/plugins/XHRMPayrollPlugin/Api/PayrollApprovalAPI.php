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
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\PayrollRun;
use XHRM\Payroll\Api\Model\PayrollRunModel;
use XHRM\Payroll\Traits\Service\PayrollServiceTrait;
use DateTime;

class PayrollApprovalAPI extends Endpoint
{
    use EntityManagerHelperTrait;
    use PayrollServiceTrait;
    use AuthUserTrait;

    public function getAll(): EndpointResult
    {
        // Not used
        return new EndpointCollectionResult(PayrollRunModel::class, []);
    }

    /**
     * Approve or reject a payroll run
     */
    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, 'id');
        $run = $this->getPayrollService()->getPayrollDao()->getPayrollRunById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($run, PayrollRun::class);

        $action = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'action');
        $note = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, 'note');

        if ($action === 'approve') {
            $run->setStatus('approved');
            $run->setApprovedAt(new DateTime());
            $empNumber = $this->getAuthUser()->getEmpNumber();
            $run->getDecorator()->setApprovedByEmpNumber($empNumber);
        } elseif ($action === 'reject') {
            $run->setStatus('rejected');
            $run->setRejectionNote($note);
        }

        $this->getPayrollService()->getPayrollDao()->savePayrollRun($run);

        return new EndpointResourceResult(PayrollRunModel::class, $run);
    }

    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule('id', new Rule(Rules::POSITIVE)),
            new ParamRule('action', new Rule(Rules::STRING_TYPE), new Rule(Rules::IN, [['approve', 'reject']])),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule('note', new Rule(Rules::STRING_TYPE)),
                true
            ),
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }
}
