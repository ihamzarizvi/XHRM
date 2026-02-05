<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.orangehrm.com
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

use XHRM\Core\Service\EmailQueueService;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Core\Traits\CacheTrait;
use XHRM\Core\Traits\LoggerTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Framework\Event\AbstractEventSubscriber;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MailerSubscriber extends AbstractEventSubscriber
{
    use LoggerTrait;
    use AuthUserTrait;
    use EntityManagerHelperTrait;
    use CacheTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => [
                ['onTerminateEvent', 0],
            ],
        ];
    }

    /**
     * @param TerminateEvent $event
     */
    public function onTerminateEvent(TerminateEvent $event): void
    {
        $cacheItem = $this->getCache()->getItem('core.send_email');

        if ($cacheItem->isHit() && $cacheItem->get()) {
            $timeStart = microtime(true);
            $this->getLogger()->info("MailerSubscriber >> Start: $timeStart");

            $emailQueueService = new EmailQueueService();
            $emailQueueService->sendAllPendingMails();

            $timeEnd = microtime(true);
            $executionTime = ($timeEnd - $timeStart);
            $this->getLogger()->info("MailerSubscriber >> End: $timeEnd");
            $this->getLogger()->info("MailerSubscriber >> Execution time: $executionTime");

            $cacheItem->set(false);
            $this->getCache()->save($cacheItem);
        }
    }
}
