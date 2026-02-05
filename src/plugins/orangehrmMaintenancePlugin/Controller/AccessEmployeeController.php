<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.orangehrm.com
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

namespace XHRM\Maintenance\Controller;

use XHRM\Authentication\Controller\AdminPrivilegeController;
use XHRM\Authentication\Controller\Traits\AdministratorAccessTrait;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;

class AccessEmployeeController extends AbstractVueController implements AdminPrivilegeController
{
    use AuthUserTrait;
    use AdministratorAccessTrait;
    use ConfigServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('access-employee-search');

        $component->addProp(
            new Prop('instance-identifier', Prop::TYPE_STRING, $this->getConfigService()->getInstanceIdentifier())
        );

        $this->setComponent($component);
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        if (!$this->getAuthUser()->getHasAdminAccess()) {
            return $this->forwardToAdministratorAccess($request);
        }
        return parent::handle($request);
    }
}
