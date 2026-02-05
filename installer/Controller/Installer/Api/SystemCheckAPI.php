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

namespace XHRM\Installer\Controller\Installer\Api;

use XHRM\Framework\Http\Request;
use XHRM\Installer\Util\StateContainer;
use XHRM\Installer\Util\SystemCheck;

class SystemCheckAPI extends \XHRM\Installer\Controller\Upgrader\Api\SystemCheckAPI
{
    /**
     * @inheritDoc
     */
    protected function handleGet(Request $request): array
    {
        $dbInfo = StateContainer::getInstance()->getDbInfo();
        if (isset($dbInfo[StateContainer::ENABLE_DATA_ENCRYPTION]) && $dbInfo[StateContainer::ENABLE_DATA_ENCRYPTION] == true) {
            $systemCheck = new SystemCheck();
            $response = parent::handleGet($request);
            $response['data'][1]['checks'][] = [
                'label' => 'Write Permissions for ???lib/confs/cryptokeys???',
                'value' => $systemCheck->isWritableCryptoKeyDir()
            ];
            return $response;
        }

        return parent::handleGet($request);
    }
}
