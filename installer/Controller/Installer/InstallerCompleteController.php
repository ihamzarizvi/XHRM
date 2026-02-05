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

namespace XHRM\Installer\Controller\Installer;

use XHRM\Config\Config;
use XHRM\Core\Helper\VueControllerHelper;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Installer\Controller\AbstractInstallerVueController;
use XHRM\Installer\Util\Logger;
use XHRM\Installer\Util\StateContainer;

class InstallerCompleteController extends AbstractInstallerVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('installer-complete-screen');
        $component->addProp(
            new Prop(VueControllerHelper::PRODUCT_VERSION, Prop::TYPE_STRING, Config::PRODUCT_VERSION)
        );
        $this->setComponent($component);
        StateContainer::getInstance()->setCurrentScreen(self::INSTALLATION_COMPLETE_SCREEN);
        StateContainer::getInstance()->clean();

        Logger::getLogger()->info(
            'XHRM ' . Config::PRODUCT_VERSION . ' status: ' . var_export(Config::isInstalled(), true)
        );
    }
}
