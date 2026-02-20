<?php

namespace XHRM\Payroll\Service;

use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Payroll\Dao\PayrollDao;

class PayrollService
{
    use NormalizerServiceTrait;

    private ?PayrollDao $payrollDao = null;

    /**
     * @return PayrollDao
     */
    public function getPayrollDao(): PayrollDao
    {
        if (!$this->payrollDao instanceof PayrollDao) {
            $this->payrollDao = new PayrollDao();
        }
        return $this->payrollDao;
    }
}
