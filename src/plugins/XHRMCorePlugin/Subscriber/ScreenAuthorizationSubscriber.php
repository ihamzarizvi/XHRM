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

namespace XHRM\Core\Subscriber;

use XHRM\Authentication\Controller\ForbiddenController;
use XHRM\Authentication\Exception\ForbiddenException;
use XHRM\Core\Authorization\Controller\CapableViewController;
use XHRM\Core\Authorization\Dto\ResourcePermission;
use XHRM\Core\Controller\AbstractViewController;
use XHRM\Core\Controller\PublicControllerInterface;
use XHRM\Core\Traits\ControllerTrait;
use XHRM\Core\Traits\ModuleScreenHelperTrait;
use XHRM\Core\Traits\Service\TextHelperTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\Framework\Event\AbstractEventSubscriber;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ScreenAuthorizationSubscriber extends AbstractEventSubscriber
{
    use ServiceContainerTrait;
    use UserRoleManagerTrait;
    use TextHelperTrait;
    use ModuleScreenHelperTrait;
    use ControllerTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['onControllerEvent', 80000],
            ],
            KernelEvents::EXCEPTION => [
                ['onExceptionEvent', 0],
            ],
        ];
    }

    /**
     * @param ControllerEvent $event
     */
    public function onControllerEvent(ControllerEvent $event)
    {
        if ($this->getControllerInstance($event) instanceof PublicControllerInterface) {
            return;
        }

        $module = $this->getCurrentModuleAndScreen()->getModule();
        $screen = $this->getCurrentModuleAndScreen()->getScreen();

        if ($module === 'auth' && $screen == 'logout') {
            return;
        }

        if (($controller = $this->getControllerInstance($event)) instanceof AbstractViewController) {
            $permissions = $this->getUserRoleManager()->getScreenPermissions($module, $screen);

            if (!$permissions instanceof ResourcePermission || !$permissions->canRead()) {
                throw new ForbiddenException();
            }

            if ($controller instanceof CapableViewController) {
                if (!$controller->isCapable($event->getRequest())) {
                    throw new ForbiddenException();
                }
            }
        }
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onExceptionEvent(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ForbiddenException) {
            $response = $this->forward(ForbiddenController::class . '::handle');
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * @param ControllerEvent $event
     * @return mixed
     */
    private function getControllerInstance(ControllerEvent $event)
    {
        return $event->getController()[0];
    }
}

