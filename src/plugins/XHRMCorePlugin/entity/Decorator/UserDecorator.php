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

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Employee;
use XHRM\Entity\User;
use XHRM\Entity\UserRole;

class UserDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var string|null
     */
    private ?string $nonHashedPassword = null;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    protected function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param int $empNumber
     */
    public function setEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getUser()->setEmployee($employee);
    }

    /**
     * @param int $id
     */
    public function setUserRoleById(int $id): void
    {
        /** @var UserRole|null $userRole */
        $userRole = $this->getReference(UserRole::class, $id);
        $this->getUser()->setUserRole($userRole);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->getUser()->getUserRole()->getName() === 'Admin';
    }

    /**
     * @return string|null
     */
    public function getNonHashedPassword(): ?string
    {
        return $this->nonHashedPassword;
    }

    /**
     * @param string|null $nonHashedPassword
     */
    public function setNonHashedPassword(?string $nonHashedPassword): void
    {
        $this->nonHashedPassword = $nonHashedPassword;
    }
}

