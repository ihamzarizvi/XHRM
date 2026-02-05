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

namespace XHRM\Claim\Controller;

use XHRM\Admin\Service\PayGradeService;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Services;

class AssignClaimController extends AbstractVueController
{
    use ServiceContainerTrait;
    use AuthUserTrait;

    /**
     * @return PayGradeService
     */
    public function getPayGradeService(): PayGradeService
    {
        return $this->getContainer()->get(Services::PAY_GRADE_SERVICE);
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $currencies = $this->getPayGradeService()->getCurrencyArray();
        $component = new Component('assign-claim-request');
        $component->addProp(new Prop('currencies', Prop::TYPE_ARRAY, $currencies));
        $component->addProp(new Prop('auth-employee-number', Prop::TYPE_NUMBER, $this->getAuthUser()->getEmpNumber()));
        $this->setComponent($component);
    }
}

