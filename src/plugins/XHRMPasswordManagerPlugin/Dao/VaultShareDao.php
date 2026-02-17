<?php

namespace XHRM\PasswordManager\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\PasswordManager\Entity\VaultShare;

class VaultShareDao extends BaseDao
{
    /**
     * @param int $userId
     * @return VaultShare[]
     */
    public function getSharesByRecipient(int $userId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
            ->from(VaultShare::class, 's')
            ->where('s.sharedWithUser = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('s.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @return VaultShare[]
     */
    public function getSharesBySharer(int $userId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
            ->from(VaultShare::class, 's')
            ->where('s.sharedByUser = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('s.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param VaultShare $share
     */
    public function save(VaultShare $share): void
    {
        $this->getEntityManager()->persist($share);
        $this->getEntityManager()->flush();
    }

    /**
     * @param VaultShare $share
     */
    public function delete(VaultShare $share): void
    {
        $this->getEntityManager()->remove($share);
        $this->getEntityManager()->flush();
    }

    public function find(int $id): ?VaultShare
    {
        return $this->getEntityManager()->find(VaultShare::class, $id);
    }

    /**
     * @return int
     */
    public function countAll(): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return (int) $qb->select('count(s.id)')
            ->from(VaultShare::class, 's')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
