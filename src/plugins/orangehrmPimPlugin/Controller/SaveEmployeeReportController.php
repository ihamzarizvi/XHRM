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

namespace XHRM\Pim\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Service\ReportGeneratorServiceTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;
use XHRM\I18N\Traits\Service\I18NHelperTrait;

class SaveEmployeeReportController extends AbstractVueController
{
    use I18NHelperTrait;
    use ReportGeneratorServiceTrait;
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $component = new Component("employee-report-edit");
            $component->addProp(new Prop("report-id", Prop::TYPE_NUMBER, $request->attributes->getInt('id')));
        } else {
            $component = new Component("employee-report-save");
        }

        $selectionCriteria = $this->getReportGeneratorService()->getReportGeneratorDao()
            ->getAllFilterFields();
        $component->addProp(
            new Prop(
                'selection-criteria',
                Prop::TYPE_ARRAY,
                array_map(
                    fn($criteria) => [
                        'id' => $criteria->getId(),
                        'key' => $criteria->getName(),
                        'label' => $this->getI18NHelper()
                            ->transBySource(ucwords(str_replace('_', ' ', $criteria->getName())))
                    ],
                    $selectionCriteria
                )
            )
        );

        $displayFieldGroups = $this->getReportGeneratorService()
            ->getReportGeneratorDao()->getAllDisplayFieldGroups();

        $component->addProp(
            new Prop(
                'display-field-groups',
                Prop::TYPE_ARRAY,
                array_map(
                    fn($displayFieldGroup) => [
                        'id' => $displayFieldGroup->getId(),
                        'label' => $this->getI18NHelper()->transBySource($displayFieldGroup->getName())
                    ],
                    $displayFieldGroups
                )
            )
        );

        $displayFields = $this->getReportGeneratorService()->getDisplayFields();

        $component->addProp(
            new Prop(
                'display-fields',
                Prop::TYPE_ARRAY,
                array_map(
                    fn(array $displayField) => [
                        'field_group_id' => $displayField['field_group_id'],
                        'fields' => array_map(
                            fn(array $field) => [
                                'id' => $field['id'],
                                'label' => $field['label'],
                            ],
                            $displayField['fields']
                        )
                    ],
                    $displayFields
                )
            )
        );

        $this->setComponent($component);
    }
}
