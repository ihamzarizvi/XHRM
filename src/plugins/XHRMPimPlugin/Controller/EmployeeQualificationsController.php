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

namespace XHRM\Pim\Controller;

use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\EmployeeLanguage;
use XHRM\Framework\Http\Request;

class EmployeeQualificationsController extends BaseViewEmployeeController
{
    public function preRender(Request $request): void
    {
        $empNumber = $request->attributes->get('empNumber');
        if ($empNumber) {
            $component = new Component('employee-qualifications');
            $fluencies = array_map(
                function ($item, $index) {
                    return [
                        "id" => $index,
                        "label" => $item,
                    ];
                },
                EmployeeLanguage::FLUENCIES,
                array_keys(EmployeeLanguage::FLUENCIES)
            );
            $competencies = array_map(
                function ($item, $index) {
                    return [
                        "id" => $index,
                        "label" => $item,
                    ];
                },
                EmployeeLanguage::COMPETENCIES,
                array_keys(EmployeeLanguage::COMPETENCIES)
            );

            $component->addProp(new Prop('emp-number', Prop::TYPE_NUMBER, $empNumber));
            $component->addProp(new Prop('fluencies', Prop::TYPE_ARRAY, $fluencies));
            $component->addProp(new Prop('competencies', Prop::TYPE_ARRAY, $competencies));

            $this->setComponent($component);

            $this->setPermissionsForEmployee(
                [
                    'qualification_work',
                    'qualification_education',
                    'qualification_skills',
                    'qualification_languages',
                    'qualification_license',
                    'qualification_license',
                    'qualifications_attachment',
                    'qualifications_custom_fields'
                ],
                $empNumber
            );
        } else {
            $this->handleBadRequest();
        }
    }

    /**
     * @inheritDoc
     */
    protected function getDataGroupsForCapabilityCheck(): array
    {
        return [
            'qualification_work',
            'qualification_education',
            'qualification_skills',
            'qualification_languages',
            'qualification_license',
            'qualification_license',
        ];
    }
}

