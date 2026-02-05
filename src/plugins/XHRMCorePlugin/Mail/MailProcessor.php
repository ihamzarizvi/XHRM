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

namespace XHRM\Core\Mail;

use XHRM\Framework\Event\Event;

interface MailProcessor
{
    /**
     * @param string $emailName
     * @param AbstractRecipient $recipient
     * @param string $recipientRole
     * @param string $performerRole
     * @param Event $event
     * @return array
     */
    public function getReplacements(
        string $emailName,
        AbstractRecipient $recipient,
        string $recipientRole,
        string $performerRole,
        Event $event
    ): array;

    /**
     * @param string $emailName
     * @param string $recipientRole
     * @param string $performerRole
     * @param Event $event
     * @return AbstractRecipient[]
     */
    public function getRecipients(string $emailName, string $recipientRole, string $performerRole, Event $event): array;
}

