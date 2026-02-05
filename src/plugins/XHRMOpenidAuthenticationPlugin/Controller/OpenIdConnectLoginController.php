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

namespace XHRM\OpenidAuthentication\Controller;

use Jumbojett\OpenIDConnectClientException;
use XHRM\Authentication\Auth\User as AuthUser;
use XHRM\Core\Api\V2\Exception\BadRequestException;
use XHRM\Core\Authorization\Service\HomePageService;
use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Controller\PublicControllerInterface;
use XHRM\Core\Traits\Auth\AuthUserTrait;
use XHRM\Framework\Http\RedirectResponse;
use XHRM\Framework\Http\Request;
use XHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;

class OpenIdConnectLoginController extends AbstractVueController implements PublicControllerInterface
{
    use AuthUserTrait;
    use SocialMediaAuthenticationServiceTrait;

    /**
     * @var HomePageService|null
     */
    protected ?HomePageService $homePageService = null;

    /**
     * @return HomePageService
     */
    public function getHomePageService(): HomePageService
    {
        return $this->homePageService ??= new HomePageService();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws BadRequestException
     * @throws OpenIDConnectClientException
     */
    public function handle(Request $request): RedirectResponse
    {
        $providerId = $request->attributes->get('providerId');

        $provider = $this->getSocialMediaAuthenticationService()->getAuthProviderDao()
            ->getAuthProviderDetailsByProviderId($providerId);
        if ($provider === null) {
            throw new BadRequestException();
        }

        $oidcClient = $this->getSocialMediaAuthenticationService()->initiateAuthentication(
            $provider,
            $this->getSocialMediaAuthenticationService()->getScope(),
            $this->getSocialMediaAuthenticationService()->getRedirectURL()
        );

        $this->getAuthUser()->setAttribute(AuthUser::OPENID_PROVIDER_ID, $provider->getId());
        $oidcClient->authenticate();

        //redirect to consent always
        $authUrl = $oidcClient->getGeneratedAuthUrl() . '&prompt=consent';
        return new RedirectResponse($authUrl);
    }
}

