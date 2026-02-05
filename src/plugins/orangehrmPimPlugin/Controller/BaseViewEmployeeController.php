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

namespace XHRM\Pim\Controller;

use Exception;
use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Helper\VueControllerHelper;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Employee;
use XHRM\Framework\Http\Request;
use XHRM\Pim\Service\PIMLeftMenuService;

abstract class BaseViewEmployeeController extends AbstractVueController implements CapableViewController
{
    use ConfigServiceTrait;
    use UserRoleManagerTrait;

    /**
     * @var PIMLeftMenuService|null
     */
    protected ?PIMLeftMenuService $pimLeftMenuService = null;

    /**
     * @return PIMLeftMenuService|null
     */
    public function getPimLeftMenuService(): ?PIMLeftMenuService
    {
        if (!$this->pimLeftMenuService instanceof PIMLeftMenuService) {
            $this->pimLeftMenuService = new PIMLeftMenuService();
        }
        return $this->pimLeftMenuService;
    }

    /**
     * @inheritDoc
     */
    public function render(Request $request): string
    {
        $empNumber = $request->attributes->get('empNumber');
        if (empty($empNumber)) {
            throw new Exception('`empNumber` required attribute for ' . __METHOD__);
        }
        $menuTabs = $this->getPimLeftMenuService()->getPreparedMenuItems($empNumber);
        $this->getComponent()->addProp(
            new Prop('tabs', Prop::TYPE_ARRAY, $menuTabs)
        );
        $this->getComponent()->addProp(
            new Prop('allowed-file-types', Prop::TYPE_ARRAY, $this->getConfigService()->getAllowedFileTypes())
        );
        $this->getComponent()->addProp(
            new Prop('max-file-size', Prop::TYPE_NUMBER, $this->getConfigService()->getMaxAttachmentSize())
        );
        return parent::render($request);
    }

    /**
     * @return string[]
     */
    abstract protected function getDataGroupsForCapabilityCheck(): array;

    /**
     * @inheritDoc
     */
    public function isCapable(Request $request): bool
    {
        $empNumber = $request->attributes->get('empNumber');
        if (!$this->isEmployeeAccessible($empNumber)) {
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        $permission = $this->getUserRoleManagerHelper()->getDataGroupPermissionsForEmployee(
            $this->getDataGroupsForCapabilityCheck(),
            $empNumber
        );
        return $permission->canRead();
    }

    /**
     * @param array $dataGroups
     * @param int $empNumber
     */
    protected function setPermissionsForEmployee(array $dataGroups, int $empNumber)
    {
        $permissions = $this->getUserRoleManagerHelper()->getDataGroupPermissionCollectionForEmployee(
            $dataGroups,
            $empNumber
        );
        $this->getContext()->set(
            VueControllerHelper::PERMISSIONS,
            $permissions->toArray()
        );
    }

    /**
     * @param int|null $empNumber
     * @return bool
     */
    protected function isEmployeeAccessible(?int $empNumber): bool
    {
        return $this->getUserRoleManager()->isEntityAccessible(Employee::class, $empNumber) ||
            $this->getUserRoleManagerHelper()->isSelfByEmpNumber($empNumber);
    }
}
