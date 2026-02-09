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

use XHRM\Framework\Event\AbstractEventSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber extends AbstractEventSubscriber
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onExceptionEvent', -65],
            ],
        ];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onExceptionEvent(ExceptionEvent $event)
    {
        //new \Symfony\Component\HttpKernel\EventListener\ErrorListener()
        $exception = $event->getThrowable();
        $response = $event->hasResponse() ? $event->getResponse() : new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent(json_encode([
                'error' => [
                    'message' => $exception->getMessage(),
                ]
            ]));
            $response->headers->set('Content-Type', 'application/json');
        }

        $event->setResponse($response);
    }
}

