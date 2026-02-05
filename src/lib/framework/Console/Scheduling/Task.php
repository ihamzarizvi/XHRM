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

use Crunz\Event;
use XHRM\Framework\Console\ArrayInput;
use XHRM\Framework\Console\Command;
use XHRM\Framework\Console\Console;
use XHRM\Framework\Logger\LoggerFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Task extends Event
{
    private Console $console;
    private OutputInterface $commandOutput;
    private CommandInfo $commandInfo;
    private Command $consoleCommand;

    public function __construct(string $id, CommandInfo $commandInfo, Console $console, OutputInterface $output)
    {
        $this->commandInfo = $commandInfo;
        $this->console = $console;
        $this->commandOutput = $output;

        $this->consoleCommand = $this->console->find($this->commandInfo->getCommand());
        parent::__construct($id, '');
    }

    /**
     * Return exit code
     *
     * @return int
     */
    public function start(): int
    {
        $input = $this->commandInfo->getInput() ?? new ArrayInput([]);
        try {
            return $this->consoleCommand->run($input, $this->commandOutput);
        } catch (Throwable $e) {
            $logger = LoggerFactory::getLogger('scheduler');
            $logger->error($e->getMessage());
            $logger->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * @return CommandInfo
     */
    public function getCommand(): CommandInfo
    {
        return $this->commandInfo;
    }
}

