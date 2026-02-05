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

namespace XHRM\Time\Api\Model;

use XHRM\Core\Api\V2\Serializer\CollectionNormalizable;
use XHRM\Core\Api\V2\Serializer\ModelConstructorArgsAwareInterface;
use XHRM\Entity\ProjectActivity;
use XHRM\Time\Traits\Service\ProjectServiceTrait;

/**
 * @OA\Schema(
 *     schema="Time-CopyActivityModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="unique", type="boolean")
 * )
 */
class CopyActivityModel implements CollectionNormalizable, ModelConstructorArgsAwareInterface
{
    use ProjectServiceTrait;

    /**
     * @var ProjectActivity[]
     */
    private array $projectActivitiesForFromProject;

    /**
     * @var ProjectActivity[]
     */
    private array $duplicatedActivities;

    /**
     * @param ProjectActivity[] $projectActivitiesForFromProject
     * @param ProjectActivity[] $duplicatedActivities
     */
    public function __construct(array $projectActivitiesForFromProject, array $duplicatedActivities)
    {
        $this->projectActivitiesForFromProject = $projectActivitiesForFromProject;
        $this->duplicatedActivities = $duplicatedActivities;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $result = [];
        $duplicatedActivities = [];
        foreach ($this->duplicatedActivities as $duplicatedActivity) {
            $duplicatedActivities[$duplicatedActivity->getName()] = $duplicatedActivity->getId();
        }

        foreach ($this->projectActivitiesForFromProject as $fromProjectActivity) {
            $name = $fromProjectActivity->getName();
            $result[] = [
                'id' => $fromProjectActivity->getId(),
                'name' => $name,
                'unique' => !isset($duplicatedActivities[$name])
            ];
        }

        return $result;
    }
}

