<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\AttendanceRule;

class AttendanceRuleModel implements Normalizable
{
    use ModelTrait;

    public function __construct(AttendanceRule $rule)
    {
        $this->setEntity($rule);
        $this->setFilters([
            'id',
            'name',
            'gracePeriodMinutes',
            'halfDayHours',
            'latesPerAbsent',
            'workingDays',
            ['isDefault'],
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'gracePeriodMinutes',
            'halfDayHours',
            'latesPerAbsent',
            'workingDays',
            'isDefault',
        ]);
    }
}
