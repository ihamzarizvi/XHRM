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

namespace XHRM\Core\Api\V2\Validator\Rules;

use XHRM\Authentication\Dto\UserCredential;
use XHRM\Authentication\Traits\Service\PasswordStrengthServiceTrait;
use XHRM\Authentication\Utility\PasswordStrengthValidation;
use XHRM\Core\Traits\Service\TextHelperTrait;

class Password extends AbstractRule
{
    use TextHelperTrait;
    use PasswordStrengthServiceTrait;

    private bool $changePassword;

    public function __construct(?bool $changePassword)
    {
        $this->changePassword = $changePassword ?? true;
    }

    public function validate($input): bool
    {
        if (!$this->changePassword) {
            return true;
        }

        $passwordStrengthValidation = new PasswordStrengthValidation();
        $credentials = new UserCredential(null, $input);

        $passwordStrength = $passwordStrengthValidation->checkPasswordStrength($credentials);
        $messages = $this->getPasswordStrengthService()->checkPasswordPolicies($credentials, $passwordStrength);

        if (count($messages) === 0) {
            return true;
        }
        return false;
    }
}
