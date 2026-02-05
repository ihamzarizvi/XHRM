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

namespace XHRM\SystemCheck\Command;

use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Framework\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ToggleSystemCheckScreenCommand extends Command
{
    use ConfigServiceTrait;

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'XHRM:toggle-system-check-screen';
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription('Enable/disable the system check screen')
            ->addOption('status', null, InputOption::VALUE_NONE);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('status') === true) {
            $status = $this->getConfigService()->showSystemCheckScreen()
                ? 'System check screen enabled'
                : 'System check screen disabled';
            $this->getIO()->note($status);
            return self::SUCCESS;
        }
        $currentStatus = $this->getConfigService()->showSystemCheckScreen();
        $this->getConfigService()->setShowSystemCheckScreen(!$currentStatus);

        $status = !$currentStatus ? 'Enabled' : 'Disabled';
        $this->getIO()->note("$status system check screen");
        return self::SUCCESS;
    }
}

