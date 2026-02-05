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

namespace XHRM\Recruitment\Controller\File;

use XHRM\Core\Controller\AbstractFileController;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;
use XHRM\Recruitment\Traits\Service\RecruitmentAttachmentServiceTrait;

class VacancyAttachment extends AbstractFileController
{
    use RecruitmentAttachmentServiceTrait;

    public function handle(Request $request): Response
    {
        $attachId = $request->attributes->get('attachId');
        $response = $this->getResponse();

        if ($attachId) {
            $attachment = $this->getRecruitmentAttachmentService()
                ->getRecruitmentAttachmentDao()
                ->getVacancyAttachmentById($attachId);
            if ($attachment instanceof \XHRM\Entity\VacancyAttachment) {
                $this->setCommonHeadersToResponse(
                    $attachment->getFileName(),
                    $attachment->getFileType(),
                    $attachment->getFileSize(),
                    $response
                );
                $response->setContent($attachment->getDecorator()->getFileContent());
                return $response;
            }
        }
        return $this->handleBadRequest();
    }
}
