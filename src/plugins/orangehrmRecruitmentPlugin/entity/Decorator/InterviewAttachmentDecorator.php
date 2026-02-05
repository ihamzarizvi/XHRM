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
use XHRM\Entity\Interview;
use XHRM\Entity\InterviewAttachment;

class InterviewAttachmentDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var InterviewAttachment
     */
    protected InterviewAttachment $interviewAttachment;

    /**
     * @var string|null
     */
    protected ?string $attachmentString = null;

    /**
     * @param InterviewAttachment $interviewAttachment
     */
    public function __construct(InterviewAttachment $interviewAttachment)
    {
        $this->interviewAttachment = $interviewAttachment;
    }

    /**
     * @return string
     */
    public function getFileContent(): string
    {
        $fileContent = $this->getInterviewAttachment()->getFileContent();
        if (is_string($fileContent)) {
            return $fileContent;
        }
        if (is_null($this->attachmentString) && is_resource($fileContent)) {
            $this->attachmentString = stream_get_contents($fileContent);
        }
        return $this->attachmentString;
    }

    /**
     * @return InterviewAttachment
     */
    protected function getInterviewAttachment(): InterviewAttachment
    {
        return $this->interviewAttachment;
    }

    /**
     * @param int $id
     */
    public function setInterviewById(int $id): void
    {
        $interview = $this->getReference(Interview::class, $id);
        $this->getInterviewAttachment()->setInterview($interview);
    }
}
