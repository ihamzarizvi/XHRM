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

namespace XHRM\Core\Authorization\Service;

use Exception;
use XHRM\Admin\Traits\Service\UserServiceTrait;
use XHRM\Core\Authorization\Manager\AbstractUserRoleManager;
use XHRM\Core\Exception\ServiceException;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\ClassHelperTrait;
use XHRM\Core\Traits\LoggerTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Entity\User;

class UserRoleManagerService
{
    use ClassHelperTrait;
    use ConfigServiceTrait;
    use AuthUserTrait;
    use LoggerTrait;
    use UserServiceTrait;

    public const KEY_USER_ROLE_MANAGER_CLASS = 'authorize_user_role_manager_class';

    /**
     * @return string|null
     */
    public function getUserRoleManagerClassName(): ?string
    {
        return $this->getConfigService()->getConfigDao()->getValue(self::KEY_USER_ROLE_MANAGER_CLASS);
    }

    /**
     * @return AbstractUserRoleManager|null
     * @throws ServiceException
     */
    public function getUserRoleManager(): ?AbstractUserRoleManager
    {
        $class = $this->getUserRoleManagerClassName();
        $manager = null;

        $fallbackNamespace = 'XHRM\\Core\\Authorization\\Manager\\';
        if ($this->getClassHelper()->classExists($class, $fallbackNamespace)) {
            try {
                $class = $this->getClassHelper()->getClass($class, $fallbackNamespace);
                $manager = new $class();
            } catch (Exception $e) {
                throw new ServiceException('Exception when initializing user role manager:' . $e->getMessage());
            }
        } else {
            throw new ServiceException(sprintf('User Role Manager class %s not found.', $class));
        }

        if (!$manager instanceof AbstractUserRoleManager) {
            throw new ServiceException(
                sprintf('User Role Manager class %s is not a subclass of %s', $class, AbstractUserRoleManager::class)
            );
        }

        // Set System User object in manager
        $userId = $this->getAuthUser()->getUserId();
        if (is_null($userId)) {
            throw new ServiceException('No logged in user found.');
        }
        $systemUser = $this->getUserService()->getSystemUser($userId);

        if ($systemUser instanceof User) {
            $manager->setUser($systemUser);
        } else {
            $this->getLogger()->info('No logged in system user when creating UserRoleManager');
        }

        return $manager;
    }
}

