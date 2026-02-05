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

use XHRM\Admin\Service\PayGradeService;
use XHRM\Claim\Dto\ClaimExpenseSearchFilterParams;
use XHRM\Claim\Traits\Service\ClaimServiceTrait;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Entity\ClaimRequest;
use XHRM\Entity\CurrencyType;
use XHRM\Entity\Employee;
use XHRM\Entity\User;
use XHRM\Framework\Services;

class ClaimRequestDecorator
{
    use EntityManagerHelperTrait;
    use AuthUserTrait;
    use ClaimServiceTrait;
    use DateTimeHelperTrait;

    /**
     * @return PayGradeService
     */
    public function getPayGradeService(): PayGradeService
    {
        return $this->getContainer()->get(Services::PAY_GRADE_SERVICE);
    }

    /**
     * @var ClaimRequest
     */
    protected ClaimRequest $claimRequest;

    /**
     * @param ClaimRequest $claimRequest
     */
    public function __construct(ClaimRequest $claimRequest)
    {
        $this->claimRequest = $claimRequest;
    }

    /**
     * @return ClaimRequest
     */
    protected function getClaimRequest(): ClaimRequest
    {
        return $this->claimRequest;
    }

    /**
     * @param int $userId
     */
    public function setUserByUserId(int $userId): void
    {
        $user = $this->getReference(User::class, $userId);
        $this->getClaimRequest()->setUser($user);
    }

    /**
     * @param string $currencyId
     */
    public function setCurrencyByCurrencyId(string $currencyId): void
    {
        $this->getClaimRequest()->setCurrencyType(
            $this->getPayGradeService()->getPayGradeDao()->getCurrencyById($currencyId)
        );
    }

    /**
     * @param string $currencyId
     *
     * @return CurrencyType|null
     */
    public function getCurrencyByCurrencyId(string $currencyId): ?CurrencyType
    {
        return $this->getPayGradeService()->getPayGradeDao()->getCurrencyById($currencyId);
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getClaimRequest()->setEmployee($employee);
    }

    /**
     * @return float
     */
    public function getTotalExpense(): float
    {
        $requestId = $this->getClaimRequest()->getId();
        $claimExpenseSearchFilterParams = new ClaimExpenseSearchFilterParams();
        $claimExpenseSearchFilterParams->setRequestId($requestId);
        $totalExpense = $this->getClaimService()->getClaimDao()->getClaimExpenseTotal($claimExpenseSearchFilterParams);
        if (is_null($totalExpense)) {
            $totalExpense = 0.0;
        }
        return $totalExpense;
    }

    /**
     * @return string|null in Y-m-d format
     */
    public function getSubmittedDate(): ?string
    {
        $submittedDate = $this->getClaimRequest()->getSubmittedDate();
        if (is_null($submittedDate)) {
            return null;
        }
        return $this->getDateTimeHelper()->formatDate($submittedDate);
    }
}

