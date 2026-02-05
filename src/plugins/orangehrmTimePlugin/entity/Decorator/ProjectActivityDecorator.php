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

namespace XHRM\Entity\Decorator;

use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Entity\Project;
use XHRM\Entity\ProjectActivity;

class ProjectActivityDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var ProjectActivity
     */
    private ProjectActivity $projectActivity;

    /**
     * @param ProjectActivity $projectActivity
     */
    public function __construct(ProjectActivity $projectActivity)
    {
        $this->projectActivity = $projectActivity;
    }

    /**
     * @return ProjectActivity
     */
    protected function getProjectActivity(): ProjectActivity
    {
        return $this->projectActivity;
    }

    /**
     * @param int $projectId
     * @return void
     */
    public function setProjectById(int $projectId): void
    {
        $project = $this->getReference(Project::class, $projectId);
        $this->getProjectActivity()->setProject($project);
    }
}
