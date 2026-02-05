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

namespace XHRM\Core\Api\V2\Validator\Exceptions;

use InvalidArgumentException;
use XHRM\Core\Api\V2\Validator\Rules\AbstractRule;

class ValidationException extends InvalidArgumentException
{
    /**
     * @var mixed
     */
    protected $input = null;

    /**
     * @var null|AbstractRule
     */
    protected ?AbstractRule $rule = null;

    /**
     * @param $input
     * @param AbstractRule $rule
     * @param string $message
     */
    public function __construct($input, AbstractRule $rule, $message = "")
    {
        $this->input = $input;
        $this->rule = $rule;
        parent::__construct($message);
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     */
    public function setInput($input): void
    {
        $this->input = $input;
    }

    /**
     * @return AbstractRule|null
     */
    public function getRule(): ?AbstractRule
    {
        return $this->rule;
    }

    /**
     * @param AbstractRule|null $rule
     */
    public function setRule(?AbstractRule $rule): void
    {
        $this->rule = $rule;
    }
}

