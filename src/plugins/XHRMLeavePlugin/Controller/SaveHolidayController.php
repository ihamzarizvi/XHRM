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

namespace XHRM\Leave\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Holiday;
use XHRM\Framework\Http\Request;
use XHRM\Core\Traits\Service\ConfigServiceTrait;

class SaveHolidayController extends AbstractVueController
{
    use ConfigServiceTrait;

    public const HOLIDAY_LENGTH_LIST = [
        ['id' => Holiday::HOLIDAY_HALF_DAY_LENGTH, 'label' => Holiday::HOLIDAY_HALF_DAY_LENGTH_NAME],
        ['id' => Holiday::HOLIDAY_FULL_DAY_LENGTH, 'label' => Holiday::HOLIDAY_FULL_DAY_LENGTH_NAME],
    ];

    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $component = new Component('holiday-edit');
            $component->addProp(new Prop('holiday-id', Prop::TYPE_NUMBER, $request->attributes->getInt('id')));
        } else {
            $component = new Component('holiday-save');
        }
        $component->addProp(new Prop('holiday-length-list', Prop::TYPE_ARRAY, self::HOLIDAY_LENGTH_LIST));
        $this->setComponent($component);
    }
}

