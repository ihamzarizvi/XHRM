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

namespace XHRM\Core\Command;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use XHRM\Config\Config;
use XHRM\Core\Traits\ORM\EntityManagerTrait;
use XHRM\Framework\Console\Command;
use XHRM\Installer\Util\V1\LanguageHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableTestLanguagePackCommand extends Command
{
    use EntityManagerTrait;

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'XHRM:enable-test-lang-pack';
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (Config::PRODUCT_MODE === Config::MODE_PROD) {
            $this->getIO()->error('Not allowed to run in `prod` mode');
            return self::FAILURE;
        }

        $langHelper = new LanguageHelper($this->getEntityManager()->getConnection());
        try {
            $langHelper->createTestLanguagePack();
        } catch (UniqueConstraintViolationException $e) {
            $this->getIO()->warning('Already executed');
            $deleteTestLanguagePack = $this->io->confirm('Delete test language pack?', false);
            if ($deleteTestLanguagePack) {
                $langHelper->deleteTestLanguagePack();
                $this->getIO()->success('Successfully deleted test language package');
                return self::SUCCESS;
            }
            return self::INVALID;
        }

        $this->getIO()->success('Successfully created test language package');
        return self::SUCCESS;
    }
}

