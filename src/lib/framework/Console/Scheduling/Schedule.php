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

use DateTimeZone;
use InvalidArgumentException;
use XHRM\Framework\Console\Console;
use Symfony\Component\Console\Output\OutputInterface;

use function array_filter;
use function array_key_exists;
use function uniqid;

class Schedule
{
    private Console $console;
    private OutputInterface $output;
    /**
     * @var Task[]
     */
    private array $tasks = [];

    /**
     * @param Console $console
     * @param OutputInterface $output
     */
    public function __construct(Console $console, OutputInterface $output)
    {
        $this->console = $console;
        $this->output = $output;
    }

    /**
     * @param CommandInfo $commandInfo
     * @return Task
     */
    public function add(CommandInfo $commandInfo): Task
    {
        if ($commandInfo->getCommand() === 'XHRM:run-schedule') {
            throw new InvalidArgumentException('Invalid command');
        }

        $id = $this->generateUniqueId($commandInfo->getCommand());
        $this->tasks[$id] = $task = new Task($id, $commandInfo, $this->console, $this->output);
        return $task;
    }

    /**
     * @return Task[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param Task[] $tasks
     */
    public function setTasks(array $tasks): void
    {
        $this->tasks = [];
        foreach ($tasks as $task) {
            $this->tasks[$task->getId()] = $task;
        }
    }

    /**
     * @param DateTimeZone $timeZone
     * @return Task[]
     */
    public function getDueTasks(DateTimeZone $timeZone): array
    {
        return array_filter(
            $this->tasks,
            static function (Task $task) use ($timeZone) {
                return $task->isDue($timeZone);
            }
        );
    }

    /**
     * @return string
     */
    protected function generateUniqueId(string $command): string
    {
        while (true) {
            $id = uniqid("XHRM_$command", true);
            if (!array_key_exists($id, $this->tasks)) {
                return $id;
            }
        }
    }
}

