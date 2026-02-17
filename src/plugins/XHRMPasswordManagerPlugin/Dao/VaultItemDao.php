<?php

namespace XHRM\PasswordManager\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\PasswordManager\Entity\VaultItem;

class VaultItemDao extends BaseDao
{
    /**
     * @param int $userId
     * @return VaultItem[]
     */
    public function getItemsByUserId(int $userId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i')
            ->from(VaultItem::class, 'i')
            ->where('i.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('i.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @return VaultItem[]
     */
    public function getFavoriteItemsByUserId(int $userId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i')
            ->from(VaultItem::class, 'i')
            ->where('i.user = :userId')
            ->andWhere('i.favorite = :favorite')
            ->setParameter('userId', $userId)
            ->setParameter('favorite', true)
            ->orderBy('i.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function countItemsByUserId(int $userId): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(i.id)')
            ->from(VaultItem::class, 'i')
            ->where('i.user = :userId')
            ->setParameter('userId', $userId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param VaultItem $item
     */
    public function save(VaultItem $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    /**
     * @param VaultItem $item
     */
    public function delete(VaultItem $item): void
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }

    public function find(int $id): ?VaultItem
    {
        return $this->getEntityManager()->find(VaultItem::class, $id);
    }

    /**
     * @return array
     */
    public function getGlobalStats(): array
    {
        $em = $this->getEntityManager();

        $totalItems = $em->createQueryBuilder()
            ->select('count(i.id)')
            ->from(VaultItem::class, 'i')
            ->getQuery()
            ->getSingleScalarResult();

        $userCount = $em->createQueryBuilder()
            ->select('count(DISTINCT IDENTITY(i.user))')
            ->from(VaultItem::class, 'i')
            ->getQuery()
            ->getSingleScalarResult();

        $avgStrength = $em->createQueryBuilder()
            ->select('avg(i.passwordStrength)')
            ->from(VaultItem::class, 'i')
            ->where('i.passwordStrength IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $breachedCount = $em->createQueryBuilder()
            ->select('count(i.id)')
            ->from(VaultItem::class, 'i')
            ->where('i.breachDetected = true')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalItems' => (int) $totalItems,
            'userCount' => (int) $userCount,
            'avgStrength' => (float) $avgStrength,
            'breachedCount' => (int) $breachedCount
        ];
    }
}
