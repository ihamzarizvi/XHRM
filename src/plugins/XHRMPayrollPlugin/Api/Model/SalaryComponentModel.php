<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\SalaryComponent;

class SalaryComponentModel implements Normalizable
{
    use ModelTrait;

    public function __construct(SalaryComponent $salaryComponent)
    {
        $this->setEntity($salaryComponent);
        $this->setFilters([
            'id',
            'name',
            'code',
            'type',
            'calculationType',
            'defaultValue',
            'formula',
            ['isTaxable'],
            ['isActive'],
            'sortOrder',
            'appliesTo',
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'code',
            'type',
            'calculationType',
            'defaultValue',
            'formula',
            'isTaxable',
            'isActive',
            'sortOrder',
            'appliesTo',
        ]);
    }
}
