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

namespace XHRM\Installer\Controller;

use InvalidArgumentException;
use XHRM\Config\Config;
use XHRM\Core\Traits\ControllerTrait;
use XHRM\Framework\Http\RedirectResponse;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Response;

abstract class AbstractInstallerController
{
    use ControllerTrait;

    /**
     * @var Response|RedirectResponse|null
     */
    protected $response = null;

    /**
     * @return Response
     */
    protected function getNewResponse(): Response
    {
        return new Response();
    }

    /**
     * @return Response|RedirectResponse
     */
    protected function getResponse()
    {
        if (!($this->response instanceof Response || $this->response instanceof RedirectResponse)) {
            $this->response = $this->getNewResponse();
        }
        return $this->response;
    }

    /**
     * @param RedirectResponse|Response|null $response
     */
    protected function setResponse($response): void
    {
        if (!($response instanceof Response ||
            $response instanceof RedirectResponse ||
            is_null($response))
        ) {
            throw new InvalidArgumentException(
                'Only allowed null, ' . Response::class . ', ' . RedirectResponse::class
            );
        }

        $this->response = $response;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function handle(Request $request)
    {
        $ignoredPaths = ['/upgrader/complete', '/installer/complete', '/'];
        if (Config::isInstalled() && !in_array($request->getPathInfo(), $ignoredPaths)) {
            $this->getResponse()->setStatusCode(Response::HTTP_BAD_GATEWAY);
            return $this->getResponse();
        }
        return $this->execute($request);
    }

    /**
     * @param Request $request
     * @return Response|RedirectResponse
     */
    abstract protected function execute(Request $request);
}
