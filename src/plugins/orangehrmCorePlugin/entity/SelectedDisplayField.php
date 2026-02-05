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

namespace XHRM\Entity;

use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\Decorator\DecoratorTrait;
use XHRM\Entity\Decorator\SelectedDisplayFieldDecorator;

/**
 * @method SelectedDisplayFieldDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_selected_display_field")
 * @ORM\Entity
 */
class SelectedDisplayField
{
    use DecoratorTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var DisplayField
     *
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\DisplayField", inversedBy="selectedDisplayFields")
     * @ORM\JoinColumn(name="display_field_id", referencedColumnName="display_field_id")
     */
    private DisplayField $displayField;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\Report")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="report_id")
     */
    private Report $report;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return DisplayField
     */
    public function getDisplayField(): DisplayField
    {
        return $this->displayField;
    }

    /**
     * @param DisplayField $displayField
     */
    public function setDisplayField(DisplayField $displayField): void
    {
        $this->displayField = $displayField;
    }

    /**
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report): void
    {
        $this->report = $report;
    }
}
