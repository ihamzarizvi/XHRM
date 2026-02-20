<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\Payslip;

class PayslipModel implements Normalizable
{
    use ModelTrait;

    public function __construct(Payslip $payslip)
    {
        $this->setEntity($payslip);
        $this->setFilters([
            'id',
            'payPeriodType',
            'basicSalary',
            'grossSalary',
            'totalDeductions',
            'netSalary',
            'currencyId',
            'totalWorkingDays',
            'daysPresent',
            'daysAbsent',
            'daysLeave',
            'daysHalf',
            'lateCount',
            'overtimeHours',
            'overtimeAmount',
            'taxAmount',
            'status',
            ['getEmployee', 'getFirstName'],
            ['getEmployee', 'getLastName'],
            ['getEmployee', 'getEmployeeId'],
        ]);
        $this->setAttributeNames([
            'id',
            'payPeriodType',
            'basicSalary',
            'grossSalary',
            'totalDeductions',
            'netSalary',
            'currencyId',
            'totalWorkingDays',
            'daysPresent',
            'daysAbsent',
            'daysLeave',
            'daysHalf',
            'lateCount',
            'overtimeHours',
            'overtimeAmount',
            'taxAmount',
            'status',
            ['employee', 'firstName'],
            ['employee', 'lastName'],
            ['employee', 'employeeId'],
        ]);
    }
}
