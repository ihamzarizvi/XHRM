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

use XHRM\Authentication\Dto\UserCredential;
use XHRM\Core\Exception\KeyHandlerException;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Installer\Util\AppSetupUtility;
use XHRM\Installer\Util\DataRegistrationUtility;
use XHRM\Installer\Util\StateContainer;

class ConfigFileAPI extends \XHRM\Installer\Controller\Upgrader\Api\ConfigFileAPI
{
    /**
     * @inheritDoc
     */
    protected function handlePost(Request $request): array
    {
        if (StateContainer::getInstance()->isSetDbInfo()) {
            $dbInfo = StateContainer::getInstance()->getDbInfo();

            if ($dbInfo[StateContainer::ENABLE_DATA_ENCRYPTION]) {
                try {
                    $appSetupUtility = new AppSetupUtility();
                    $appSetupUtility->writeKeyFile();
                } catch (KeyHandlerException $exception) {
                    $this->getResponse()->setStatusCode(Response::HTTP_CONFLICT);
                    return
                        [
                            'error' => [
                                'status' => $this->getResponse()->getStatusCode(),
                                'message' => $exception->getMessage()
                            ]
                        ];
                }
            }

            $dbUser = $dbInfo[StateContainer::XHRM_DB_USER] ?? $dbInfo[StateContainer::DB_USER];
            $dbPassword = isset($dbInfo[StateContainer::XHRM_DB_USER])
                ? $dbInfo[StateContainer::XHRM_DB_PASSWORD]
                : $dbInfo[StateContainer::DB_PASSWORD];
            StateContainer::getInstance()->storeDbInfo(
                $dbInfo[StateContainer::DB_HOST],
                $dbInfo[StateContainer::DB_PORT],
                new UserCredential($dbUser, $dbPassword),
                $dbInfo[StateContainer::DB_NAME]
            );
        }
        return parent::handlePost($request);
    }

    /**
     * @inheritDoc
     */
    protected function getRegistrationType(): int
    {
        return DataRegistrationUtility::REGISTRATION_TYPE_INSTALLER_STARTED;
    }
}
