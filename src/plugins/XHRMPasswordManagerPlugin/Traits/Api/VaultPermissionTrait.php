<?php

namespace XHRM\PasswordManager\Traits\Api;

use XHRM\PasswordManager\Service\VaultPermissionService;
use XHRM\Core\Traits\ServiceContainerTrait;

trait VaultPermissionTrait
{
    // Ensure we can access container
    use ServiceContainerTrait;

    protected function getVaultPermissionService(): VaultPermissionService
    {
        return $this->getContainer()->get(VaultPermissionService::class);
    }

    protected function checkVaultAccess(): void
    {
        // Requires class to implement UserRoleManagerTrait or similar to get user
        // We assume usage in Endpoint that has UserRoleManagerTrait
        $user = $this->getUserRoleManager()->getUser();
        $this->getVaultPermissionService()->ensureVaultAccess($user);
    }

    protected function checkAdminAccess(): void
    {
        $user = $this->getUserRoleManager()->getUser();
        $this->getVaultPermissionService()->ensureAdminAccess($user);
    }
}
