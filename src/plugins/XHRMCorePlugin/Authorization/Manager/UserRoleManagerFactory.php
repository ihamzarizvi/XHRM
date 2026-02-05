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

namespace XHRM\Core\Authorization\Manager;

use XHRM\Core\Authorization\Service\UserRoleManagerService;
use XHRM\Core\Exception\ServiceException;

/**
 * Provides access to configured user role manager class.
 *
 */
class UserRoleManagerFactory
{
    /**
     * @var null|AbstractUserRoleManager
     */
    private static ?AbstractUserRoleManager $userRoleManager = null;

    /**
     * @return AbstractUserRoleManager
     * @throws ServiceException
     */
    public static function getUserRoleManager(): AbstractUserRoleManager
    {
        if (!self::$userRoleManager instanceof AbstractUserRoleManager) {
            $userRoleManagerService = new UserRoleManagerService();
            self::$userRoleManager = $userRoleManagerService->getUserRoleManager();
        }
        return self::$userRoleManager;
    }

    /**
     * Get new user role manager when session detail changes
     *
     * @return AbstractUserRoleManager
     * @throws ServiceException
     */
    public static function getNewUserRoleManager(): AbstractUserRoleManager
    {
        $userRoleManagerService = new UserRoleManagerService();
        return $userRoleManagerService->getUserRoleManager();
    }

    /**
     * Update current user role manager when session detail changes
     * @return AbstractUserRoleManager
     * @throws ServiceException
     */
    public static function updateUserRoleManager(): AbstractUserRoleManager
    {
        self::$userRoleManager = self::getNewUserRoleManager();
        return self::$userRoleManager;
    }
}

