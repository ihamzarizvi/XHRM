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

    /**
     * @param int $id
     * @return VaultShare|null
     */
    public function find(int $id): ?VaultShare
    {
        return $this->getEntityManager()->find(VaultShare::class, $id);
    }

    /**
     * @param int $itemId
     * @param int $userId
     * @return VaultShare|null
     */
    public function findShare(int $itemId, int $userId): ?VaultShare
    {
        return $this->getEntityManager()->getRepository(VaultShare::class)->findOneBy([
            'vaultItem' => $itemId,
            'sharedWithUser' => $userId
        ]);
    }
}
