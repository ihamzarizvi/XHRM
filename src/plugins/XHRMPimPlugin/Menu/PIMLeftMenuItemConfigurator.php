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

namespace XHRM\Pim\Menu;

use XHRM\Core\Menu\MenuConfigurator;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ControllerTrait;
use XHRM\Core\Traits\ModuleScreenHelperTrait;
use XHRM\Core\Traits\Service\MenuServiceTrait;
use XHRM\Entity\MenuItem;
use XHRM\Entity\Screen;

class PIMLeftMenuItemConfigurator implements MenuConfigurator
{
    use MenuServiceTrait;
    use ControllerTrait;
    use AuthUserTrait;
    use ModuleScreenHelperTrait;

    /**
     * @inheritDoc
     */
    public function configure(Screen $screen): ?MenuItem
    {
        if ($this->getAuthUser()->getEmpNumber() == $this->getCurrentRequest()->attributes->get('empNumber')) {
            return $this->getMenuService()->getMenuDao()->getMenuItemByTitle('My Info', 1);
        }
        $this->getCurrentModuleAndScreen()->overrideScreen('viewEmployeeList');
        return null;
    }
}

