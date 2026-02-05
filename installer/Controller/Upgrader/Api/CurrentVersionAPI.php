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

namespace XHRM\Installer\Controller\Upgrader\Api;

use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Installer\Controller\AbstractInstallerRestController;
use XHRM\Installer\Util\AppSetupUtility;
use XHRM\Installer\Util\StateContainer;

class CurrentVersionAPI extends AbstractInstallerRestController
{
    /**
     * @inheritDoc
     */
    protected function handleGet(Request $request): array
    {
        $currentVersion = StateContainer::getInstance()->getCurrentVersion();
        if (!is_null($currentVersion)) {
            return [
                'version' => $currentVersion,
                'fromDatabase' => false,
            ];
        }

        if (!StateContainer::getInstance()->isSetDbInfo()) {
            $this->getResponse()->setStatusCode(Response::HTTP_CONFLICT);
            return
                [
                    'error' => [
                        'status' => $this->getResponse()->getStatusCode(),
                        'message' => 'Database info not yet stored'
                    ]
                ];
        }

        $appSetupUtility = new AppSetupUtility();
        return [
            'version' => $appSetupUtility->getCurrentProductVersionFromDatabase(),
            'fromDatabase' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function handlePost(Request $request): array
    {
        $currentVersion = $request->request->get('currentVersion');
        StateContainer::getInstance()->setCurrentVersion($currentVersion);
        return [
            'version' => $currentVersion,
        ];
    }
}
