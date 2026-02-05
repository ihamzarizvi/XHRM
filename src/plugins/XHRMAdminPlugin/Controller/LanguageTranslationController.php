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

namespace XHRM\Admin\Controller;

use XHRM\Admin\Traits\Service\LocalizationServiceTrait;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\Common\NoRecordsFoundController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;

class LanguageTranslationController extends AbstractVueController
{
    use LocalizationServiceTrait;

    public function preRender(Request $request): void
    {
        if ($request->attributes->has('languageId')) {
            $languageId = $request->attributes->getInt('languageId');
            $component = new Component('language-translation-edit');
            $component->addProp(new Prop('language-id', Prop::TYPE_NUMBER, $languageId));
            $language = $this->getLocalizationService()->getLocalizationDao()
                ->getLanguageById($languageId);
            $languagePackage = $language->getName();
            if (!$language->isAdded() || !$language->isEnabled()) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }
            $sourceLanguage = 'English (United States)';
            $component->addProp(new Prop('language-package', Prop::TYPE_STRING, $languagePackage));
            $component->addProp(new Prop('source-language', Prop::TYPE_STRING, $sourceLanguage));
            $this->setComponent($component);
        }
    }
}

