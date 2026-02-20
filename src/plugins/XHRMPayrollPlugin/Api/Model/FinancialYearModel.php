<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\FinancialYear;

class FinancialYearModel implements Normalizable
{
    use ModelTrait;

    public function __construct(FinancialYear $fy)
    {
        $this->setEntity($fy);
        $this->setFilters([
            'id',
            'label',
            ['getStartDate', 'format', 'Y-m-d'],
            ['getEndDate', 'format', 'Y-m-d'],
            'status',
        ]);
        $this->setAttributeNames([
            'id',
            'label',
            'startDate',
            'endDate',
            'status',
        ]);
    }
}
