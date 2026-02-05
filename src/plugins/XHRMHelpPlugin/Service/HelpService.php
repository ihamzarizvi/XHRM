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

namespace XHRM\Help\Service;

use XHRM\Help\Processor\HelpProcessor;

class HelpService
{
    protected ?HelpConfigService $helpConfigService = null;
    public ?HelpProcessor $helpProcessorClass = null;

    /**
     * @return HelpConfigService
     */
    public function getHelpConfigService(): HelpConfigService
    {
        return $this->helpConfigService ??= new HelpConfigService();
    }

    /**
     * @return HelpProcessor
     */
    public function getHelpProcessor(): HelpProcessor
    {
        if (!$this->helpProcessorClass instanceof HelpProcessor) {
            $helpProcessorClassName = 'XHRM\\Help\\Processor\\' . $this->getHelpConfigService()->getHelpProcessorClass();
            $this->helpProcessorClass = new $helpProcessorClassName();
        }
        return $this->helpProcessorClass;
    }

    /**
     * @param string $label
     * @return string
     */
    public function getRedirectUrl(string $label): string
    {
        return $this->getHelpProcessor()->getRedirectUrl($label);
    }

    /**
     * @return string
     */
    public function getDefaultRedirectUrl(): string
    {
        return $this->getHelpProcessor()->getDefaultRedirectUrl();
    }

    /**
     * @return bool
     */
    public function isValidUrl(): bool
    {
        return filter_var($this->getHelpConfigService()->getBaseHelpUrl(), FILTER_VALIDATE_URL);
    }
}

