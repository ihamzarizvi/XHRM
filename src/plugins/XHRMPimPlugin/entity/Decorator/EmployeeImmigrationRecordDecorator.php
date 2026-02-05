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

namespace XHRM\Entity\Decorator;

use Exception;
use XHRM\Admin\Service\CountryService;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\EmployeeImmigrationRecord;
use XHRM\Framework\Services;

class EmployeeImmigrationRecordDecorator
{
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;
    use ServiceContainerTrait;

    /**
     * @var EmployeeImmigrationRecord
     */
    protected EmployeeImmigrationRecord $employeeImmigrationRecord;

    /**
     * EmployeeImmigrationRecordDecorator constructor.
     * @param EmployeeImmigrationRecord $employeeImmigrationRecord
     */
    public function __construct(EmployeeImmigrationRecord $employeeImmigrationRecord)
    {
        $this->employeeImmigrationRecord = $employeeImmigrationRecord;
    }

    /**
     * @return EmployeeImmigrationRecord
     */
    protected function getEmployeeImmigrationRecord(): EmployeeImmigrationRecord
    {
        return $this->employeeImmigrationRecord;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmployeeImmigrationRecord()->setEmployee($employee);
    }

    /**
     * @return string|null
     */
    public function getIssuedDate(): ?string
    {
        $date = $this->getEmployeeImmigrationRecord()->getIssuedDate();
        return $this->getDateTimeHelper()->formatDate($date);
    }

    /**
     * @return string|null
     */
    public function getExpiryDate(): ?string
    {
        $date = $this->getEmployeeImmigrationRecord()->getExpiryDate();
        return $this->getDateTimeHelper()->formatDate($date);
    }

    /**
     * @return string|null
     */
    public function getReviewDate(): ?string
    {
        $date = $this->getEmployeeImmigrationRecord()->getReviewDate();
        return $this->getDateTimeHelper()->formatDate($date);
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getCountryName(): ?string
    {
        $countryCode = $this->getEmployeeImmigrationRecord()->getCountryCode();
        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        if (is_null($countryCode)) {
            return null;
        }
        $country = $countryService->getCountryByCountryCode($countryCode);
        return $country ? $country->getCountryName() : null;
    }

    /**
     * @return string|null
     */
    public function getDocumentType(): ?string
    {
        $type = $this->getEmployeeImmigrationRecord()->getType();
        return EmployeeImmigrationRecord::DOCUMENT_TYPE_MAP[$type] ?? null;
    }
}

