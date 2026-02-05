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

namespace XHRM\OAuth\Controller\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use XHRM\Core\Controller\AbstractController;
use XHRM\Core\Controller\PublicControllerInterface;
use XHRM\Framework\Http\Request;
use XHRM\OAuth\Traits\OAuthServerTrait;
use XHRM\OAuth\Traits\PsrHttpFactoryHelperTrait;
use Throwable;

class TokenController extends AbstractController implements PublicControllerInterface
{
    use OAuthServerTrait;
    use PsrHttpFactoryHelperTrait;

    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        try {
            $server = $this->getOAuthServer()->getServer();
            $psrRequest = $this->getPsrHttpFactoryHelper()->createPsr7Request($request);
            $psrResponse = $this->getPsrHttpFactoryHelper()->createPsr7Response($this->getResponse());
            $psrResponse = $server->respondToAccessTokenRequest($psrRequest, $psrResponse);

            return $this->getPsrHttpFactoryHelper()->createResponseFromPsr7Response($psrResponse);
        } catch (OAuthServerException $e) {
            $psrResponse = $this->getPsrHttpFactoryHelper()->createPsr7Response($this->getResponse());
            return $this->getPsrHttpFactoryHelper()
                ->createResponseFromPsr7Response($e->generateHttpResponse($psrResponse));
        } catch (Throwable $e) {
            $psrResponse = $this->getPsrHttpFactoryHelper()->createPsr7Response($this->getResponse());
            return $this->getPsrHttpFactoryHelper()
                ->createResponseFromPsr7Response(
                    (OAuthServerException::serverError('An unexpected error has occurred'))
                        ->generateHttpResponse($psrResponse)
                );
        }
    }
}

