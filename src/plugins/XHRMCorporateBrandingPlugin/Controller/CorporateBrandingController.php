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

namespace XHRM\CorporateBranding\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Entity\Theme;
use XHRM\Framework\Http\Request;

class CorporateBrandingController extends AbstractVueController
{
    use ConfigServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('corporate-branding');
        $component->addProp(new Prop('allowed-image-types', Prop::TYPE_ARRAY, Theme::ALLOWED_IMAGE_TYPES));
        $component->addProp(new Prop('aspect-ratios', Prop::TYPE_OBJECT, [
            'clientLogo' => Theme::CLIENT_LOGO_ASPECT_RATIO,
            'clientBanner' => Theme::CLIENT_BANNER_ASPECT_RATIO,
            'loginBanner' => Theme::LOGIN_BANNER_ASPECT_RATIO,
        ]));
        $component->addProp(new Prop('aspect-ratio-tolerance', Prop::TYPE_NUMBER, Theme::IMAGE_ASPECT_RATIO_TOLERANCE));
        $component->addProp(new Prop('max-file-size', Prop::TYPE_NUMBER, $this->getConfigService()->getMaxAttachmentSize()));
        $this->setComponent($component);
    }
}

