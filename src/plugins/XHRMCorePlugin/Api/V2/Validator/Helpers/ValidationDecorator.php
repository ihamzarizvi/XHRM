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

namespace XHRM\Core\Api\V2\Validator\Helpers;

use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;

class ValidationDecorator
{
    /**
     * @param ParamRule $paramRule
     * @param bool $excludeEmptyString
     * @return ParamRule
     */
    public function requiredParamRule(ParamRule $paramRule, bool $excludeEmptyString = false): ParamRule
    {
        $paramRule->setRules(
            [
                new Rule(Rules::REQUIRED, [$excludeEmptyString]),
                new Rule($paramRule->getCompositeClass(), [...$paramRule->getRules()])
            ]
        );
        $paramRule->setCompositeClass(Rules::ALL_OF);
        return $paramRule;
    }

    /**
     * @param ParamRule $paramRule
     * @param bool $excludeEmptyString If true consider empty string as acceptable value,
     * If false consider empty string as null while validating
     *
     * @return ParamRule
     */
    public function notRequiredParamRule(ParamRule $paramRule, bool $excludeEmptyString = false): ParamRule
    {
        $paramRule->setRules(
            [
                new Rule(Rules::NOT_REQUIRED, [$excludeEmptyString]),
                new Rule($paramRule->getCompositeClass(), [...$paramRule->getRules()])
            ]
        );
        $paramRule->setCompositeClass(Rules::ANY_OF);
        return $paramRule;
    }
}

