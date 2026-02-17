<?php

namespace XHRM\PasswordManager\Service;

use XHRM\Core\Authorization\Service\ScreenPermissionService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use XHRM\Entity\User;

class VaultPermissionService
{
    private ScreenPermissionService $screenPermissionService;

    public function __construct(ScreenPermissionService $screenPermissionService)
    {
        $this->screenPermissionService = $screenPermissionService;
    }

    /**
     * Checks if the user has access to the Password Vault.
     * Throws AccessDeniedHttpException if not.
     *
     * @param User $user
     * @throws AccessDeniedHttpException
     */
    public function ensureVaultAccess(User $user): void
    {
        // Check 'View Password Vault' permission
        $permissions = $this->screenPermissionService->getScreenPermissions(
            'password_manager',
            '/password-manager',
            [$user->getUserRole()]
        );

        if (!$permissions->canRead()) {
            throw new AccessDeniedHttpException('You do not have permission to access the Password Vault.');
        }
    }

    /**
     * Checks if the user has Admin access to the Password Manager.
     * Throws AccessDeniedHttpException if not.
     *
     * @param User $user
     * @throws AccessDeniedHttpException
     */
    public function ensureAdminAccess(User $user): void
    {
        // Check 'Password Manager Admin' permission
        $permissions = $this->screenPermissionService->getScreenPermissions(
            'password_manager',
            '/password-manager/admin',
            [$user->getUserRole()]
        );

        if (!$permissions->canRead()) {
            throw new AccessDeniedHttpException('You do not have permission to access Password Manager settings.');
        }
    }

    /**
     * Returns true if user has Admin settings access.
     *
     * @param User $user
     * @return bool
     */
    public function isAdmin(User $user): bool
    {
        try {
            $this->ensureAdminAccess($user);
            return true;
        } catch (AccessDeniedHttpException $e) {
            return false;
        }
    }
}
