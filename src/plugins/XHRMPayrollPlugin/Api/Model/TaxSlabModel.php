<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\TaxSlab;

class TaxSlabModel implements Normalizable
{
    use ModelTrait;

    public function __construct(TaxSlab $slab)
    {
        $this->setEntity($slab);
        $this->setFilters([
            'id',
            'minIncome',
            'maxIncome',
            'taxRate',
            'fixedAmount',
            ['getFinancialYear', 'getId'],
            ['getFinancialYear', 'getLabel'],
        ]);
        $this->setAttributeNames([
            'id',
            'minIncome',
            'maxIncome',
            'taxRate',
            'fixedAmount',
            ['financialYear', 'id'],
            ['financialYear', 'label'],
        ]);
    }
}
