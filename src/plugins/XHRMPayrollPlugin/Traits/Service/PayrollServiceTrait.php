<?php

namespace XHRM\Payroll\Traits\Service;

use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Services;
use XHRM\Payroll\Service\PayrollService;

trait PayrollServiceTrait
{
    use ServiceContainerTrait;

    private ?PayrollService $payrollService = null;

    /**
     * @return PayrollService
     */
    public function getPayrollService(): PayrollService
    {
        if (!$this->payrollService instanceof PayrollService) {
            $this->payrollService = $this->getContainer()->get(Services::PAYROLL_SERVICE);
        }
        return $this->payrollService;
    }
}
