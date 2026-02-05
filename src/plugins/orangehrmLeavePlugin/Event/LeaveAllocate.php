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

namespace XHRM\Leave\Event;

use XHRM\Entity\LeaveRequest;
use XHRM\Entity\User;
use XHRM\Entity\WorkflowStateMachine;
use XHRM\Framework\Event\Event;
use XHRM\Leave\Dto\LeaveRequest\DetailedLeaveRequest;

abstract class LeaveAllocate extends Event
{
    /**
     * @var LeaveRequest|DetailedLeaveRequest
     */
    private $leaveRequest;

    /**
     * @var WorkflowStateMachine
     */
    private WorkflowStateMachine $workflow;

    /**
     * @var User
     */
    private User $performer;

    /**
     * @param LeaveRequest|DetailedLeaveRequest $leaveRequest
     * @param WorkflowStateMachine $workflow
     * @param User $performer
     */
    public function __construct($leaveRequest, WorkflowStateMachine $workflow, User $performer)
    {
        $this->leaveRequest = $leaveRequest;
        $this->workflow = $workflow;
        $this->performer = $performer;
    }

    /**
     * @return DetailedLeaveRequest
     */
    public function getDetailedLeaveRequest(): DetailedLeaveRequest
    {
        if ($this->leaveRequest instanceof LeaveRequest) {
            $this->leaveRequest = new DetailedLeaveRequest($this->leaveRequest);
            $this->leaveRequest->fetchLeaves();
        }
        return $this->leaveRequest;
    }

    /**
     * @return WorkflowStateMachine
     */
    public function getWorkflow(): WorkflowStateMachine
    {
        return $this->workflow;
    }

    /**
     * @return User
     */
    public function getPerformer(): User
    {
        return $this->performer;
    }
}
