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

namespace XHRM\Framework\Console\Scheduling;

use XHRM\Framework\Console\ArrayInput;

class CommandInfo
{
    private string $command;
    private ?ArrayInput $input;

    /**
     * @param string $command
     * @param ArrayInput|null $input e.g. $input = new ArrayInput(['arg1' => 'buz', 'foo' => 'bar', '--bar' => 'foobar']);
     */
    public function __construct(string $command, ?ArrayInput $input = null)
    {
        $this->command = $command;
        $this->input = $input;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return ArrayInput|null
     */
    public function getInput(): ?ArrayInput
    {
        return $this->input;
    }
}

