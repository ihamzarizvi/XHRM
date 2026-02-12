<?php

namespace XHRM\PasswordManager\Service;

use XHRM\Core\Traits\EventDispatcherTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\Service\NormalizerServiceTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\PasswordManager\Dao\VaultCategoryDao;
use XHRM\PasswordManager\Dao\VaultItemDao;
use XHRM\PasswordManager\Dao\VaultShareDao;
use XHRM\PasswordManager\Dao\VaultUserKeyDao;
use XHRM\PasswordManager\Entity\VaultCategory;
use XHRM\PasswordManager\Entity\VaultItem;

class PasswordManagerService
{
    use EventDispatcherTrait;
    use ConfigServiceTrait;
    use NormalizerServiceTrait;
    use UserRoleManagerTrait;

    protected ?VaultItemDao $vaultItemDao = null;
    protected ?VaultCategoryDao $vaultCategoryDao = null;
    protected ?VaultShareDao $vaultShareDao = null;
    protected ?VaultUserKeyDao $vaultUserKeyDao = null;

    public function getVaultItemDao(): VaultItemDao
    {
        if (is_null($this->vaultItemDao)) {
            $this->vaultItemDao = new VaultItemDao();
        }
        return $this->vaultItemDao;
    }

    public function getVaultCategoryDao(): VaultCategoryDao
    {
        if (is_null($this->vaultCategoryDao)) {
            $this->vaultCategoryDao = new VaultCategoryDao();
        }
        return $this->vaultCategoryDao;
    }

    public function getVaultShareDao(): VaultShareDao
    {
        if (is_null($this->vaultShareDao)) {
            $this->vaultShareDao = new VaultShareDao();
        }
        return $this->vaultShareDao;
    }

    public function getVaultUserKeyDao(): VaultUserKeyDao
    {
        if (is_null($this->vaultUserKeyDao)) {
            $this->vaultUserKeyDao = new VaultUserKeyDao();
        }
        return $this->vaultUserKeyDao;
    }

    /**
     * @param VaultItem $item
     * @return VaultItem
     */
    public function saveVaultItem(VaultItem $item): VaultItem
    {
        $this->getVaultItemDao()->save($item);
        return $item;
    }

    /**
     * @param int $userId
     * @return VaultItem[]
     */
    public function getVaultItems(int $userId): array
    {
        return $this->getVaultItemDao()->getItemsByUserId($userId);
    }

    /**
     * @param int $vaultItemId
     * @return VaultItem|null
     */
    public function getVaultItemById(int $vaultItemId): ?VaultItem
    {
        return $this->getVaultItemDao()->find($vaultItemId);
    }

    /**
     * @param int $userId
     * @return VaultCategory[]
     */
    public function getCategories(int $userId): array
    {
        return $this->getVaultCategoryDao()->getCategoriesByUserId($userId);
    }

    /**
     * @param VaultCategory $category
     * @return VaultCategory
     */
    public function saveCategory(VaultCategory $category): VaultCategory
    {
        $this->getVaultCategoryDao()->save($category);
        return $category;
    }

    /**
     * @param int $categoryId
     * @return VaultCategory|null
     */
    public function getVaultCategoryById(int $categoryId): ?VaultCategory
    {
        return $this->getVaultCategoryDao()->find($categoryId);
    }

    /**
     * @param VaultItem $item
     */
    public function deleteVaultItem(VaultItem $item): void
    {
        $this->getVaultItemDao()->delete($item);
    }

    /**
     * @param int[] $ids
     */
    public function deleteVaultItems(array $ids): void
    {
        foreach ($ids as $id) {
            $item = $this->getVaultItemById($id);
            if ($item) {
                $this->deleteVaultItem($item);
            }
        }
    }


    /**
     * @param VaultCategory $category
     */
    public function deleteCategory(VaultCategory $category): void
    {
        $this->getVaultCategoryDao()->delete($category);
    }

    /**
     * @param int $userId
     * @return \XHRM\Entity\User|null
     */
    public function getUserById(int $userId): ?\XHRM\Entity\User
    {
        return $this->getVaultUserKeyDao()->findUser($userId);
    }
}
