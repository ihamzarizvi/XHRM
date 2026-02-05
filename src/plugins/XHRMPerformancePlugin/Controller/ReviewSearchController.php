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

namespace XHRM\Performance\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Service\DateTimeHelperTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;

class ReviewSearchController extends AbstractVueController
{
    use DateTimeHelperTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('review-search');
        $this->addFromToDateProps($component);
        $this->setComponent($component);
    }

    /**
     * @param Component $component
     */
    protected function addFromToDateProps(Component $component): void
    {
        $currentYear = $this->getDateTimeHelper()->getNow()->format('Y');
        $component->addProp(new Prop('from-date', Prop::TYPE_STRING, "$currentYear-01-01"));
        $component->addProp(new Prop('to-date', Prop::TYPE_STRING, "$currentYear-12-31"));
    }
}

