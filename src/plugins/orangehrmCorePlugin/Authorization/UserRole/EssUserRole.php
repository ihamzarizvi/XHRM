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

namespace XHRM\Core\Authorization\UserRole;

use XHRM\Buzz\Traits\Service\BuzzServiceTrait;
use XHRM\Dashboard\Traits\Service\QuickLaunchServiceTrait;
use XHRM\Entity\PerformanceReview;
use XHRM\Performance\Traits\Service\PerformanceReviewServiceTrait;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Entity\PerformanceTracker;
use XHRM\Entity\PerformanceTrackerLog;
use XHRM\Performance\Traits\Service\PerformanceTrackerLogServiceTrait;
use XHRM\Performance\Traits\Service\PerformanceTrackerServiceTrait;

class EssUserRole extends AbstractUserRole
{
    use AuthUserTrait;
    use PerformanceTrackerServiceTrait;
    use PerformanceTrackerLogServiceTrait;
    use PerformanceReviewServiceTrait;
    use QuickLaunchServiceTrait;
    use BuzzServiceTrait;

    public const ALLOWED_REVIEW_STATUSES = 'allowed_review_statuses';

    /**
     * @inheritDoc
     */
    protected function getAccessibleIdsForEntity(string $entityType, array $requiredPermissions = []): array
    {
        switch ($entityType) {
            case PerformanceReview::class:
                return $this->getAccessibleReviewIds($requiredPermissions);
            case PerformanceTracker::class:
                return $this->getAccessiblePerformanceTrackerIdsForESS($requiredPermissions);
            case PerformanceTrackerLog::class:
                return $this->getAccessiblePerformanceTrackerLogIdsForESS($requiredPermissions);
            default:
                return [];
        }
    }

    /**
     * @return int[]
     */
    protected function getAccessibleReviewIds(array $requiredPermissions = []): array
    {
        $allowedStatuses = [];
        if (
            isset($requiredPermissions[self::ALLOWED_REVIEW_STATUSES]) &&
            is_array($requiredPermissions[self::ALLOWED_REVIEW_STATUSES])
        ) {
            $allowedStatuses = $requiredPermissions[self::ALLOWED_REVIEW_STATUSES];
        }

        $empNumber = $this->getEmployeeNumber();
        return $this->getPerformanceReviewService()
            ->getPerformanceReviewDao()
            ->getSelfReviewIds($empNumber, $allowedStatuses);
    }

    /**
     * @param array $requiredPermissions
     * @return int[]
     */
    protected function getAccessiblePerformanceTrackerIdsForESS(array $requiredPermissions = []): array
    {
        return $this->getPerformanceTrackerService()
            ->getPerformanceTrackerDao()
            ->getTrackerIdsByEmpNumber($this->getAuthUser()->getEmpNumber());
    }

    /**
     * @param array $requiredPermissions
     * @return int[]
     */
    protected function getAccessiblePerformanceTrackerLogIdsForESS(array $requiredPermissions = []): array
    {
        return $this->getPerformanceTrackerLogService()
            ->getPerformanceTrackerLogDao()
            ->getPerformanceTrackerLogIdsByUserId($this->getAuthUser()->getUserId());
    }

    /**
     * @inheritDoc
     */
    public function getAccessibleQuickLaunchList(array $requiredPermissions): array
    {
        return $this->getQuickLaunchService()
            ->getQuickLaunchDao()
            ->getQuickLaunchListForESS();
    }
}
