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

namespace XHRM\Core\Report\FilterField;

use XHRM\Admin\Service\SkillService;
use XHRM\Entity\Skill;
use XHRM\ORM\QueryBuilderWrapper;

class EmployeeSkill extends FilterField implements ValueXNormalizable
{
    /**
     * @inheritDoc
     */
    public function addWhereToQueryBuilder(QueryBuilderWrapper $queryBuilderWrapper): void
    {
        $qb = $queryBuilderWrapper->getQueryBuilder();
        if ($this->getOperator() === Operator::EQUAL && !is_null($this->getX())) {
            $qb->andWhere($qb->expr()->eq('skill.skill', ':EmployeeSkill_skill'))
                ->setParameter('EmployeeSkill_skill', $this->getX());
        }
    }

    /**
     * @inheritDoc
     */
    public function getEntityAliases(): array
    {
        return ['skill'];
    }

    /**
     * @inheritDoc
     */
    public function toArrayXValue(): ?array
    {
        $skillService = new SkillService();
        $skill = $skillService->getSkillById($this->getX());
        if ($skill instanceof Skill) {
            return [
                'id' => $skill->getId(),
                'label' => $skill->getName(),
            ];
        }
        return null;
    }
}

