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

use XHRM\Entity\Leave;
use XHRM\Entity\User;
use XHRM\Entity\WorkflowStateMachine;
use XHRM\Framework\Event\Event;

abstract class LeaveStatusChange extends Event
{
    /**
     * @var Leave[]
     */
    private array $leaves;

    /**
     * @var WorkflowStateMachine
     */
    private WorkflowStateMachine $workflow;

    /**
     * @var User
     */
    private User $performer;

    /**
     * @param Leave[] $leaves
     * @param WorkflowStateMachine $workflow
     * @param User $performer
     */
    public function __construct(array $leaves, WorkflowStateMachine $workflow, User $performer)
    {
        $this->leaves = $leaves;
        $this->workflow = $workflow;
        $this->performer = $performer;
    }

    /**
     * Leaves related to one leave request
     * @return Leave[]
     */
    public function getLeaves(): array
    {
        return $this->leaves;
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
