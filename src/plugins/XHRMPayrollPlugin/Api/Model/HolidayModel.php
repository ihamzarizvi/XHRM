<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\Holiday;

class HolidayModel implements Normalizable
{
    use ModelTrait;

    public function __construct(Holiday $holiday)
    {
        $this->setEntity($holiday);
        $this->setFilters([
            'id',
            'name',
            ['getDate', 'format', 'Y-m-d'],
            ['isRecurring'],
            ['isHalfDay'],
            'appliesTo',
            'departmentId',
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'date',
            'isRecurring',
            'isHalfDay',
            'appliesTo',
            'departmentId',
        ]);
    }
}
