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

namespace XHRM\Core\Registration\Controller;

use XHRM\Core\Controller\AbstractController;
use XHRM\Core\Registration\Processor\RegistrationEventProcessorFactory;
use XHRM\Core\Traits\CacheTrait;
use XHRM\Entity\RegistrationEventQueue;
use XHRM\Framework\Http\Response;
use Throwable;

class PushEventController extends AbstractController
{
    use CacheTrait;

    /**
     * @return Response
     */
    public function handle(): Response
    {
        $cacheItem = $this->getCache()->getItem('core.registration.event.pushed');
        if ($cacheItem->isHit()) {
            return $this->getResponse();
        }

        try {
            $registrationEventProcessorFactory = new RegistrationEventProcessorFactory();
            $registrationEventProcessor = $registrationEventProcessorFactory->getRegistrationEventProcessor(
                RegistrationEventQueue::ACTIVE_EMPLOYEE_COUNT
            );
            $registrationEventProcessor->publishRegistrationEvents();
        } catch (Throwable $e) {
        }

        $cacheItem->expiresAfter(3600);
        $cacheItem->set(true);
        $this->getCache()->save($cacheItem);
        return $this->getResponse();
    }
}

