<?php

namespace XHRM\PasswordManager\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\PasswordManager\Entity\VaultCategory;

class VaultCategoryDao extends BaseDao
{
    /**
     * @param int $userId
     * @return VaultCategory[]
     */
    public function getCategoriesByUserId(int $userId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(VaultCategory::class, 'c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param VaultCategory $category
     */
    public function save(VaultCategory $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @param VaultCategory $category
     */
    public function delete(VaultCategory $category): void
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $id
     * @return VaultCategory|null
     */
    public function find(int $id): ?VaultCategory
    {
        return $this->getEntityManager()->find(VaultCategory::class, $id);
    }
}
