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

namespace XHRM\Time\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Controller\VueComponentPermissionTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\Time\Traits\Service\ProjectServiceTrait;

class ProjectController extends AbstractVueController
{
    use VueComponentPermissionTrait;
    use ProjectServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('project-list');
        $unSelectableProjectIds = $this->getProjectService()
            ->getProjectDao()
            ->getUnselectableProjectIds();
        $component->addProp(new Prop('unselectable-ids', Prop::TYPE_ARRAY, $unSelectableProjectIds));
        $this->setComponent($component);
        $this->setPermissions(['time_projects', 'time_project_activities']);
    }
}

