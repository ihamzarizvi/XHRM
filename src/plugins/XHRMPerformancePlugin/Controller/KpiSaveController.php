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
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Performance\Traits\Service\KpiServiceTrait;

class KpiSaveController extends AbstractVueController
{
    use KpiServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $component = new Component('kpi-edit');
            $component->addProp(new Prop('kpi-id', Prop::TYPE_NUMBER, $request->attributes->getInt('id')));
        } else {
            $component = new Component('kpi-save');

            $defaultKpi = $this->getKpiService()->getKpiDao()->getDefaultKpi();
            if ($defaultKpi) {
                $component->addProp(new Prop('default-min-rating', Prop::TYPE_NUMBER, $defaultKpi->getMinRating()));
                $component->addProp(new Prop('default-max-rating', Prop::TYPE_NUMBER, $defaultKpi->getMaxRating()));
            }
        }
        $this->setComponent($component);
    }
}

