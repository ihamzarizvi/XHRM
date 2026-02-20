<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\PayrollRun;

class PayrollRunModel implements Normalizable
{
    use ModelTrait;

    public function __construct(PayrollRun $run)
    {
        $this->setEntity($run);
        $this->setFilters([
            'id',
            'periodType',
            ['getPeriodStart', 'format', 'Y-m-d'],
            ['getPeriodEnd', 'format', 'Y-m-d'],
            'status',
            'totalGross',
            'totalDeductions',
            'totalNet',
            'employeeCount',
            'currencyId',
            ['getGeneratedAt', 'format', 'Y-m-d H:i'],
        ]);
        $this->setAttributeNames([
            'id',
            'periodType',
            'periodStart',
            'periodEnd',
            'status',
            'totalGross',
            'totalDeductions',
            'totalNet',
            'employeeCount',
            'currencyId',
            'generatedAt',
        ]);
    }
}
