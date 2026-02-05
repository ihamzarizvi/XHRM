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

namespace XHRM\Dashboard\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Dashboard\Dto\ActionableReviewSearchFilterParams;
use XHRM\Entity\CandidateVacancy;
use XHRM\Entity\PerformanceReview;
use XHRM\Entity\Reviewer;
use XHRM\Entity\ReviewerGroup;
use XHRM\ORM\QueryBuilderWrapper;
use XHRM\Performance\Dto\PerformanceReviewSearchFilterParams;

class EmployeeActionSummaryDao extends BaseDao
{
    public const STATE_INTERVIEW_SCHEDULED = 'INTERVIEW SCHEDULED';

    /**
     * @param int[] $candidateIds
     * @return int
     */
    public function getActionableScheduledInterviewCount(array $candidateIds): int
    {
        $qb = $this->createQueryBuilder(CandidateVacancy::class, 'candidateVacancy');
        $qb->leftJoin('candidateVacancy.vacancy', 'vacancy');
        $qb->andWhere($qb->expr()->in('candidateVacancy.candidate', ':candidateIds'))
            ->setParameter('candidateIds', $candidateIds)
            ->andWhere('candidateVacancy.status = :status')
            ->setParameter('status', self::STATE_INTERVIEW_SCHEDULED)
            ->andWhere('vacancy.status = :active')
            ->setParameter('active', true);
        return $this->getPaginator($qb)->count();
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return int
     */
    public function getPendingAppraisalReviewCount(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): int {
        $query = $this->getPendingAppraisalReviewQueryBuilderWrapper(
            $actionableReviewSearchFilterParams
        )->getQueryBuilder();
        return $this->getPaginator($query)->count();
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return QueryBuilderWrapper
     */
    private function getPendingAppraisalReviewQueryBuilderWrapper(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): QueryBuilderWrapper {
        $qb = $this->getActionableReviewQueryBuilderWrapper($actionableReviewSearchFilterParams)->getQueryBuilder();
        if (!is_null($actionableReviewSearchFilterParams->getReviewerEmpNumber())) {
            $qb->andWhere($qb->expr()->eq('reviewerEmployee.empNumber', ':supervisorEmpNumber'))
                ->setParameter('supervisorEmpNumber', $actionableReviewSearchFilterParams->getReviewerEmpNumber());
        }
        return $this->getQueryBuilderWrapper($qb);
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return QueryBuilderWrapper
     */
    private function getActionableReviewQueryBuilderWrapper(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): QueryBuilderWrapper {
        $qb = $this->createQueryBuilder(PerformanceReview::class, 'performanceReview');
        $qb->leftJoin('performanceReview.employee', 'employee');
        $qb->leftJoin('performanceReview.reviewers', 'reviewer');
        $qb->leftJoin('reviewer.employee', 'reviewerEmployee');
        $qb->leftJoin('reviewer.group', 'reviewGroup');

        $qb->andWhere($qb->expr()->eq('reviewGroup.name', ':reviewGroupName'))
            ->setParameter('reviewGroupName', ReviewerGroup::REVIEWER_GROUP_SUPERVISOR);

        if (
            is_null($actionableReviewSearchFilterParams->getIncludeEmployees()) ||
            $actionableReviewSearchFilterParams->getIncludeEmployees() ===
            PerformanceReviewSearchFilterParams::INCLUDE_EMPLOYEES_ONLY_CURRENT
        ) {
            $qb->andWhere($qb->expr()->isNull('employee.employeeTerminationRecord'));
        } elseif (
            $actionableReviewSearchFilterParams->getIncludeEmployees() ===
            PerformanceReviewSearchFilterParams::INCLUDE_EMPLOYEES_ONLY_PAST
        ) {
            $qb->andWhere($qb->expr()->isNotNull('employee.employeeTerminationRecord'));
        }

        $qb->andWhere($qb->expr()->isNull('employee.purgedAt'));

        if (!is_null($actionableReviewSearchFilterParams->getActionableStatuses())) {
            $qb->andWhere($qb->expr()->in('performanceReview.statusId', ':reviewStatusIds'))
                ->setParameter('reviewStatusIds', $actionableReviewSearchFilterParams->getActionableStatuses());
        }
        return $this->getQueryBuilderWrapper($qb);
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return int
     */
    public function getPendingSelfReviewCount(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): int {
        $qb = $this->createQueryBuilder(Reviewer::class, 'reviewer');
        $qb->andWhere($qb->expr()->in('reviewer.review', ':reviewIds'))
            ->setParameter('reviewIds', $this->getPendingSelfReviewIds($actionableReviewSearchFilterParams));
        $qb->andWhere('reviewer.employee = :employeeId')
            ->setParameter('employeeId', $actionableReviewSearchFilterParams->getEmpNumber());
        $qb->andWhere($qb->expr()->in('reviewer.status', ':selfReviewStatuses'))
            ->setParameter('selfReviewStatuses', $actionableReviewSearchFilterParams->getSelfReviewStatuses());
        return $this->getPaginator($qb)->count();
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return QueryBuilderWrapper
     */
    private function getSelfReviewQueryBuilderWrapper(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): QueryBuilderWrapper {
        $qb = $this->getActionableReviewQueryBuilderWrapper($actionableReviewSearchFilterParams)->getQueryBuilder();
        if (!is_null($actionableReviewSearchFilterParams->getEmpNumber())) {
            $qb->andWhere($qb->expr()->eq('performanceReview.employee', ':empNumber'))
                ->setParameter('empNumber', $actionableReviewSearchFilterParams->getEmpNumber());
        }
        return $this->getQueryBuilderWrapper($qb);
    }

    /**
     * @param ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
     * @return int[]
     */
    private function getPendingSelfReviewIds(
        ActionableReviewSearchFilterParams $actionableReviewSearchFilterParams
    ): array {
        $qb = $this->getSelfReviewQueryBuilderWrapper($actionableReviewSearchFilterParams)->getQueryBuilder();
        $qb->select('performanceReview.id');
        $result = $qb->getQuery()->getArrayResult();
        return array_column($result, 'id');
    }
}

