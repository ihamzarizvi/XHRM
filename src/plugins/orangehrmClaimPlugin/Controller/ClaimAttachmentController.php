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

namespace XHRM\Claim\Controller;

use Exception;
use XHRM\Claim\Traits\Service\ClaimServiceTrait;
use XHRM\Core\Controller\AbstractFileController;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Entity\ClaimAttachment;
use XHRM\Entity\ClaimRequest;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;

class ClaimAttachmentController extends AbstractFileController
{
    use ClaimServiceTrait;
    use UserRoleManagerTrait;

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $response = $this->getResponse();

        if ($request->attributes->has('requestId') && $request->attributes->has('attachId')) {
            $requestId = $request->attributes->get('requestId');
            $attachId = $request->attributes->get('attachId');

            $claimRequest = $this->getClaimService()->getClaimDao()
                ->getClaimRequestById($requestId);
            if (!($claimRequest instanceof ClaimRequest)) {
                return $this->handleBadRequest();
            }

            if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($claimRequest->getEmployee()->getEmpNumber())) {
                return $this->handleBadRequest();
            }

            try {
                $attachment = $this->getClaimService()->getClaimDao()
                    ->getClaimAttachmentFile($requestId, $attachId);
            } catch (Exception $e) {
                return $this->handleBadRequest();
            }

            if ($attachment instanceof ClaimAttachment) {
                $this->setCommonHeadersToResponse(
                    $attachment->getFilename(),
                    $attachment->getFileType(),
                    $attachment->getSize(),
                    $response
                );
                $response->setContent($attachment->getDecorator()->getAttachment());
                return $response;
            }
        }
        return $this->handleBadRequest();
    }
}
