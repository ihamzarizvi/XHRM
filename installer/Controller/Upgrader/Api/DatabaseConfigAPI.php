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

use XHRM\Authentication\Dto\UserCredential;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Installer\Controller\AbstractInstallerRestController;
use XHRM\Installer\Exception\SystemCheckException;
use XHRM\Installer\Util\StateContainer;
use XHRM\Installer\Util\UpgraderConfigUtility;

class DatabaseConfigAPI extends AbstractInstallerRestController
{
    /**
     * @inheritDoc
     */
    protected function handlePost(Request $request): array
    {
        $dbHost = $request->request->get('dbHost');
        $dbPort = $request->request->get('dbPort');
        $dbUser = $request->request->get('dbUser');
        $dbPassword = $request->request->get('dbPassword');
        $dbName = $request->request->get('dbName');

        StateContainer::getInstance()->storeDbInfo($dbHost, $dbPort, new UserCredential($dbUser, $dbPassword), $dbName);

        $response = $this->getResponse();
        $upgraderConfigUtility = new UpgraderConfigUtility();
        try {
            $upgraderConfigUtility->checkDatabaseConnection();
        } catch (SystemCheckException $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return
                [
                    'error' => [
                        'status' => $response->getStatusCode(),
                        'message' => $e->getMessage(),
                    ]
                ];
        }

        return [
            'data' => [
                'dbHost' => $dbHost,
                'dbPort' => $dbPort,
                'dbUser' => $dbUser,
                'dbName' => $dbName,
            ],
            'meta' => []
        ];
    }

    /**
     * @inheritDoc
     */
    protected function handleGet(Request $request): array
    {
        $dbInfo = StateContainer::getInstance()->getDbInfo();
        return [
            'data' => [
                'dbHost' => $dbInfo[StateContainer::DB_HOST],
                'dbPort' => $dbInfo[StateContainer::DB_PORT],
                'dbName' => $dbInfo[StateContainer::DB_NAME],
                'dbUser' => $dbInfo[StateContainer::DB_USER],
            ],
            'meta' => []

        ];
    }
}
