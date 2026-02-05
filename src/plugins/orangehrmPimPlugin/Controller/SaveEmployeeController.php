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

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Service\IDGeneratorService;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Employee;
use XHRM\Entity\EmpPicture;
use XHRM\Framework\Http\Request;
use XHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class SaveEmployeeController extends AbstractVueController
{
    use SocialMediaAuthenticationServiceTrait;
    protected ?IDGeneratorService $idGeneratorService = null;

    /**
     * @return IDGeneratorService|null
     */
    public function getIdGeneratorService(): ?IDGeneratorService
    {
        if (!$this->idGeneratorService instanceof IDGeneratorService) {
            $this->idGeneratorService = new IDGeneratorService();
        }
        return $this->idGeneratorService;
    }

    public function preRender(Request $request): void
    {
        $component = new Component('employee-save');
        $employeeId = $this->getIdGeneratorService()->getNextID(Employee::class, false);
        $component->addProp(new Prop('emp-id', Prop::TYPE_NUMBER, $employeeId));
        $component->addProp(new Prop('allowed-image-types', Prop::TYPE_ARRAY, EmpPicture::ALLOWED_IMAGE_TYPES));

        $isPasswordRequired = !$this->getSocialMediaAuthenticationService()->isSocialMediaAuthEnable();
        $component->addProp(new Prop('is-password-required', Prop::TYPE_BOOLEAN, $isPasswordRequired));
        $this->setComponent($component);
    }
}
