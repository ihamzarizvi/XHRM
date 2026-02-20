<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\OvertimeRule;

class OvertimeRuleModel implements Normalizable
{
    use ModelTrait;

    public function __construct(OvertimeRule $rule)
    {
        $this->setEntity($rule);
        $this->setFilters([
            'id',
            'name',
            'type',
            'rateMultiplier',
            'minHoursBeforeOt',
            'maxOtHoursPerDay',
            ['isActive'],
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'type',
            'rateMultiplier',
            'minHoursBeforeOt',
            'maxOtHoursPerDay',
            'isActive',
        ]);
    }
}
