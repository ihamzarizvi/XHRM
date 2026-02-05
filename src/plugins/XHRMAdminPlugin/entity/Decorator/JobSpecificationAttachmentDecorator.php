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

namespace XHRM\Entity\Decorator;

use XHRM\Entity\JobSpecificationAttachment;

class JobSpecificationAttachmentDecorator
{
    /**
     * @var JobSpecificationAttachment
     */
    protected JobSpecificationAttachment $jobSpecification;

    /**
     * This property to read `fileContent` resource from `JobSpecificationAttachment`
     * @var string|null
     */
    protected ?string $attachmentString = null;

    /**
     * @param JobSpecificationAttachment $jobSpecification
     */
    public function __construct(JobSpecificationAttachment $jobSpecification)
    {
        $this->jobSpecification = $jobSpecification;
    }

    /**
     * @return JobSpecificationAttachment
     */
    protected function getJobSpecification(): JobSpecificationAttachment
    {
        return $this->jobSpecification;
    }

    /**
     * @return string
     */
    public function getFileContent(): string
    {
        $fileContent = $this->getJobSpecification()->getFileContent();
        if (is_string($fileContent)) {
            return $fileContent;
        }
        if (is_null($this->attachmentString) && is_resource($fileContent)) {
            $this->attachmentString = stream_get_contents($fileContent);
        }
        return $this->attachmentString;
    }
}

