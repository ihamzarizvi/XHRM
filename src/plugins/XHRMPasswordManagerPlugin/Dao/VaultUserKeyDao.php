<?php

namespace XHRM\PasswordManager\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\PasswordManager\Entity\VaultUserKey;

class VaultUserKeyDao extends BaseDao
{
    /**
     * @param int $userId
     * @return VaultUserKey|null
     */
    public function findByUserId(int $userId): ?VaultUserKey
    {
        return $this->getEntityManager()->getRepository(VaultUserKey::class)->findOneBy(['user' => $userId]);
    }

    /**
     * @param VaultUserKey $key
     */
    public function save(VaultUserKey $key): void
    {
        $this->getEntityManager()->persist($key);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $id
     * @return VaultUserKey|null
     */
    public function find(int $id): ?VaultUserKey
    {
        return $this->getEntityManager()->find(VaultUserKey::class, $id);
    }

    public function findUser(int $id): ?\XHRM\Entity\User
    {
        return $this->getEntityManager()->find(\XHRM\Entity\User::class, $id);
    }
}
