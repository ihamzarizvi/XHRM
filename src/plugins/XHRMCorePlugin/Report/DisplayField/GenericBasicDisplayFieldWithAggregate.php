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

namespace XHRM\Core\Report\DisplayField;

class GenericBasicDisplayFieldWithAggregate extends GenericBasicDisplayField
{
    public const BASIC_DISPLAY_FIELD_MAP = [
        'empLocation' => ['entityAlias' => 'location', 'field' => 'name', 'aggregate' => 'MIN'],
    ];

    private string $aggregate;

    /**
     * @param \XHRM\Entity\DisplayField $displayField
     */
    public function setDisplayField(\XHRM\Entity\DisplayField $displayField): void
    {
        $mapping = self::BASIC_DISPLAY_FIELD_MAP[$displayField->getFieldAlias()];
        $this->setEntityAlias($mapping['entityAlias']);
        $this->setField($mapping['field']);
        $this->setAggregate($mapping['aggregate']);
    }

    /**
     * @return string
     */
    public function getAggregate(): string
    {
        return $this->aggregate;
    }

    /**
     * @param string $aggregate
     */
    public function setAggregate(string $aggregate): void
    {
        $this->aggregate = $aggregate;
    }

    /**
     * @return string
     */
    public function getSelectPart(): string
    {
        return $this->getAggregate() . '(' . $this->getEntityAlias() . '.' . $this->getField() . ')';
    }
}

