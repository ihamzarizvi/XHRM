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
use XHRM\Entity\I18NLangString;
use XHRM\Entity\I18NLanguage;
use XHRM\Entity\I18NTranslation;

class I18NTranslationDecorator
{
    use EntityManagerHelperTrait;

    private I18NTranslation $i18NTranslation;

    /**
     * @param I18NTranslation $i18NTranslation
     */
    public function __construct(I18NTranslation $i18NTranslation)
    {
        $this->i18NTranslation = $i18NTranslation;
    }

    /**
     * @return I18NTranslation
     */
    protected function getI18NTranslation(): I18NTranslation
    {
        return $this->i18NTranslation;
    }

    /**
     * @param int $langStringId
     * @return void
     */
    public function setLangStringById(int $langStringId): void
    {
        $langString = $this->getReference(I18NLangString::class, $langStringId);
        $this->getI18NTranslation()->setLangString($langString);
    }

    /**
     * @param int $languageId
     * @return void
     */
    public function setLanguageById(int $languageId): void
    {
        $language = $this->getReference(I18NLanguage::class, $languageId);
        $this->getI18NTranslation()->setLanguage($language);
    }
}
