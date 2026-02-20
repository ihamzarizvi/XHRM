<?php

namespace XHRM\Payroll\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\EmployeeLoan;

class EmployeeLoanModel implements Normalizable
{
    use ModelTrait;

    public function __construct(EmployeeLoan $loan)
    {
        $this->setEntity($loan);
        $this->setFilters([
            'id',
            'loanType',
            'description',
            'totalAmount',
            'monthlyDeduction',
            'remainingAmount',
            ['getStartDate', 'format', 'Y-m-d'],
            'status',
            ['getEmployee', 'getFirstName'],
            ['getEmployee', 'getLastName'],
            ['getEmployee', 'getEmployeeId'],
        ]);
        $this->setAttributeNames([
            'id',
            'loanType',
            'description',
            'totalAmount',
            'monthlyDeduction',
            'remainingAmount',
            'startDate',
            'status',
            ['employee', 'firstName'],
            ['employee', 'lastName'],
            ['employee', 'employeeId'],
        ]);
    }
}
