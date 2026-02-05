<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace XHRM\Core\Report\DisplayField\Salary\Dto;

use XHRM\Admin\Dto\EmployeeSalarySearchFilterParams;
use XHRM\Core\Report\DisplayField\NormalizableDTO;
use XHRM\Pim\Traits\Service\EmployeeSalaryServiceTrait;

class Salary extends NormalizableDTO
{
    use EmployeeSalaryServiceTrait;

    private ?int $empNumber = null;

    /**
     * @param int|null $empNumber
     */
    public function __construct(?int $empNumber)
    {
        $this->empNumber = $empNumber;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $fields): ?array
    {
        $employeeSalarySearchFilterParams = new EmployeeSalarySearchFilterParams();
        $employeeSalarySearchFilterParams->setEmpNumber($this->empNumber);
        $employeeSalarySearchFilterParams->setLimit(0);
        $employeeSalaries = $this->getEmployeeSalaryService()
            ->getEmployeeSalaryDao()
            ->getEmployeeSalaries($employeeSalarySearchFilterParams);
        return $this->normalizeArray($employeeSalaries, $fields);
    }

    /**
     * @inheritDoc
     */
    protected function getFieldGetterMap(): array
    {
        return [
            'salSalaryComponent' => ['getSalaryName'],
            'salAmount' => ['getAmount'],
            'salComments' => ['getComment'],
            'salaryId' => ['getId'],
            'salPayGrade' => ['getPayGrade', 'getName'],
            'salPayFrequency' => ['getPayPeriod', 'getName'],
            'salCurrency' => ['getCurrencyType', 'getName'],
            'ddAccountNumber' => ['getDirectDebit', 'getAccount'],
            'ddAccountType' => ['getDirectDebit', 'getAccountType'],
            'ddRoutingNumber' => ['getDirectDebit', 'getRoutingNumber'],
            'ddAmount' => ['getDirectDebit', 'getAmount'],
        ];
    }
}

