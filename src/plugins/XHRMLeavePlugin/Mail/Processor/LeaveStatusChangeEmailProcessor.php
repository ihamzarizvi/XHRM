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

namespace XHRM\Leave\Mail\Processor;

use InvalidArgumentException;
use XHRM\Core\Mail\AbstractRecipient;
use XHRM\Core\Mail\MailProcessor;
use XHRM\Entity\Leave;
use XHRM\Framework\Event\Event;
use XHRM\Leave\Event\LeaveStatusChange;
use XHRM\Leave\Mail\Recipient;
use XHRM\Leave\Traits\Service\LeaveRequestServiceTrait;
use XHRM\Pim\Traits\Service\EmployeeServiceTrait;

class LeaveStatusChangeEmailProcessor extends AbstractLeaveEmailProcessor implements MailProcessor
{
    use EmployeeServiceTrait;
    use LeaveRequestServiceTrait;

    /**
     * @inheritDoc
     * @param LeaveStatusChange $event
     */
    public function getReplacements(
        string $emailName,
        AbstractRecipient $recipient,
        string $recipientRole,
        string $performerRole,
        Event $event
    ): array {
        if (!$event instanceof LeaveStatusChange) {
            throw new InvalidArgumentException(
                'Expected instance of `' . LeaveStatusChange::class . '` got `' . get_class($event) . '`'
            );
        }
        $replacements = [];
        $performer = $event->getPerformer()->getEmployee();
        $replacements['performerFirstName'] = $performer->getFirstName();
        $replacements['performerFullName'] = $performer->getDecorator()->getFirstAndLastNames();

        $replacements['recipientFirstName'] = $recipient->getName();
        $replacements['recipientFullName'] = $recipient->getName();
        if ($recipient instanceof Recipient) {
            $replacements['recipientFirstName'] = $recipient->getFirstName();
        }

        $leaves = $event->getLeaves();

        $applicant = $leaves[0]->getEmployee();
        $replacements['applicantFullName'] = $applicant->getDecorator()->getFirstAndLastNames();
        $replacements['assigneeFullName'] = $applicant->getDecorator()->getFirstAndLastNames();
        $replacements['leaveType'] = $leaves[0]->getLeaveType()->getName();

        $detailedLeaves = $this->getLeaveRequestService()->getDetailedLeaves(
            $leaves,
            $leaves[0]->getLeaveRequest()->getLeaves()
        );
        $replacements['leaveDetails'] = $this->getLeaveDetailsByDetailedLeaves($detailedLeaves);
        $leaveRequestId = $leaves[0]->getLeaveRequest()->getId();
        $replacements['leaveRequestComments'] = $this->getLeaveRequestComments($leaveRequestId);

        return $replacements;
    }

    /**
     * @inheritDoc
     */
    public function getRecipients(
        string $emailName,
        string $recipientRole,
        string $performerRole,
        Event $event
    ): array {
        if (!$event instanceof LeaveStatusChange) {
            throw new InvalidArgumentException(
                'Expected instance of `' . LeaveStatusChange::class . '` got `' . get_class($event) . '`'
            );
        }
        $leaves = $event->getLeaves();
        $this->verifyLeavesFromOneLeaveRequest($leaves);

        $recipients = [];
        switch ($recipientRole) {
            case 'subscriber':
                $recipients = $this->getSubscribers($emailName);
                break;
            case 'supervisor':
                $recipients = $this->getSupervisors($leaves[0]->getEmployee());
                break;
            case 'ess':
                $recipients = $this->getSelf($leaves[0]->getEmployee());
                break;
            default:
                $recipients = $this->getEmployeesWithRole(
                    $recipientRole,
                    $leaves[0]->getEmployee()
                );
                break;
        }

        return $recipients;
    }

    /**
     * @param Leave[] $leaves
     */
    private function verifyLeavesFromOneLeaveRequest(array $leaves): void
    {
        if (!isset($leaves[0])) {
            throw new InvalidArgumentException('Empty leave array provided');
        }
        $leaveRequestId = $leaves[0]->getLeaveRequest()->getId();
        for ($i = 1; $i < count($leaves); $i++) {
            if ($leaves[$i]->getLeaveRequest()->getId() !== $leaveRequestId) {
                throw new InvalidArgumentException(
                    'Leaves in ' . LeaveStatusChange::class . ' should belong to a leave request'
                );
            }
        }
    }
}

