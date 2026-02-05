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

namespace XHRM\Maintenance\Controller\File;

use XHRM\Authentication\Controller\ForbiddenController;
use XHRM\Core\Controller\AbstractFileController;
use XHRM\Core\Controller\Exception\RequestForwardableException;
use XHRM\Core\Traits\Service\TextHelperTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Maintenance\DownloadFormats\JsonDownloadFormat;
use XHRM\Maintenance\Service\MaintenanceService;

class AccessEmployeeFileController extends AbstractFileController
{
    use TextHelperTrait;
    use UserRoleManagerTrait;

    /**
     * @var MaintenanceService|null
     */
    protected ?MaintenanceService $maintenanceService = null;
    protected ?JsonDownloadFormat $downloadFormat = null;

    /**
     * @return MaintenanceService
     */

    public function getDownloadFormat(): JsonDownloadFormat
    {
        if (!$this->downloadFormat instanceof JsonDownloadFormat) {
            $this->downloadFormat = new JsonDownloadFormat();
        }
        return $this->downloadFormat;
    }

    public function getMaintenanceService(): MaintenanceService
    {
        if (!$this->maintenanceService instanceof MaintenanceService) {
            $this->maintenanceService = new MaintenanceService();
        }
        return $this->maintenanceService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        if (!$this->getUserRoleManager()->getDataGroupPermissions('maintenance_employee_json')->canRead()) {
            throw new RequestForwardableException(ForbiddenController::class . '::handle');
        }

        $empNumber = $request->attributes->get('empNumber');
        $response = $this->getResponse();

        if ($empNumber) {
            $content = $this->getDownloadFormat()->getFormattedString(
                $this->getMaintenanceService()->accessEmployeeData($empNumber)
            );
            $this->setCommonHeadersToResponse(
                $this->getDownloadFormat()->getDownloadFileName($empNumber),
                'application/json',
                $this->getTextHelper()->strLength($content),
                $response
            );
            $response->setContent($content);
            return $response;
        }

        return $this->handleBadRequest();
    }
}
