<?php

namespace XHRM\PasswordManager\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\PasswordManager\Entity\VaultAuditLog;

class VaultAuditLogDao extends BaseDao
{
    /**
     * @param VaultAuditLog $log
     */
    public function save(VaultAuditLog $log): void
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    /**
     * @param array $filters  ['userId' => int, 'action' => string, 'from' => string, 'to' => string]
     * @param int $limit
     * @param int $offset
     * @return VaultAuditLog[]
     */
    public function getAuditLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('l')
            ->from(VaultAuditLog::class, 'l')
            ->leftJoin('l.user', 'u')
            ->leftJoin('l.vaultItem', 'i')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['userId'])) {
            $qb->andWhere('u.id = :userId')->setParameter('userId', $filters['userId']);
        }
        if (!empty($filters['action'])) {
            $qb->andWhere('l.action = :action')->setParameter('action', $filters['action']);
        }
        if (!empty($filters['from'])) {
            $qb->andWhere('l.createdAt >= :from')->setParameter('from', new \DateTime($filters['from']));
        }
        if (!empty($filters['to'])) {
            $qb->andWhere('l.createdAt <= :to')->setParameter('to', new \DateTime($filters['to'] . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $filters
     * @return int
     */
    public function countAuditLogs(array $filters = []): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(l.id)')
            ->from(VaultAuditLog::class, 'l')
            ->leftJoin('l.user', 'u');

        if (!empty($filters['userId'])) {
            $qb->andWhere('u.id = :userId')->setParameter('userId', $filters['userId']);
        }
        if (!empty($filters['action'])) {
            $qb->andWhere('l.action = :action')->setParameter('action', $filters['action']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
