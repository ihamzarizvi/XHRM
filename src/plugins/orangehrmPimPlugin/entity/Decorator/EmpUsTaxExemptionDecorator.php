<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.orangehrm.com
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

use XHRM\Admin\Service\CountryService;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\EmpUsTaxExemption;
use XHRM\Framework\Services;

class EmpUsTaxExemptionDecorator
{
    use EntityManagerHelperTrait;
    use ServiceContainerTrait;

    /**
     * @var EmpUsTaxExemption
     */
    protected EmpUsTaxExemption $empUsTaxExemption;

    /**
     * EmpUsTaxExemptionDecorator constructor.
     * @param EmpUsTaxExemption $empUsTaxExemption
     */
    public function __construct(EmpUsTaxExemption $empUsTaxExemption)
    {
        $this->empUsTaxExemption = $empUsTaxExemption;
    }

    /**
     * @return EmpUsTaxExemption
     */
    protected function getEmpUsTaxExemption(): EmpUsTaxExemption
    {
        return $this->empUsTaxExemption;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getEmpUsTaxExemption()->setEmployee($employee);
    }


    /**
     * @return string|null
     */
    public function getTaxState(): ?string
    {
        $stateCode = $this->getEmpUsTaxExemption()->getState();
        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        if (is_null($stateCode)) {
            return null;
        }
        $state = $countryService->getProvinceByProvinceCode($stateCode);
        return $state->getProvinceName();
    }

    /**
     * @return string|null
     */
    public function getUnemploymentState(): ?string
    {
        $stateCode = $this->getEmpUsTaxExemption()->getUnemploymentState();
        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        if (is_null($stateCode)) {
            return null;
        }
        $state = $countryService->getProvinceByProvinceCode($stateCode);
        return $state->getProvinceName();
    }

    /**
     * @return string|null
     */
    public function getWorkState(): ?string
    {
        $stateCode = $this->getEmpUsTaxExemption()->getWorkState();
        /** @var CountryService $countryService */
        $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
        if (is_null($stateCode)) {
            return null;
        }
        $state = $countryService->getProvinceByProvinceCode($stateCode);
        return $state->getProvinceName();
    }
}
