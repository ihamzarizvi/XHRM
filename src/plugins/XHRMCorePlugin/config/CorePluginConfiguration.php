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

use XHRM\Config\Config;
use XHRM\Core\Authorization\Helper\UserRoleManagerHelper;
use XHRM\Core\Authorization\Manager\UserRoleManagerFactory;
use XHRM\Core\Command\EnableTestLanguagePackCommand;
use XHRM\Core\Command\RunScheduleCommand;
use XHRM\Core\Helper\ClassHelper;
use XHRM\Core\Registration\Subscriber\RegistrationEventPersistSubscriber;
use XHRM\Core\Service\CacheService;
use XHRM\Core\Service\ConfigService;
use XHRM\Core\Service\DateTimeHelperService;
use XHRM\Core\Service\MenuService;
use XHRM\Core\Service\ModuleService;
use XHRM\Core\Service\NormalizerService;
use XHRM\Core\Service\NumberHelperService;
use XHRM\Core\Service\ReportGeneratorService;
use XHRM\Core\Service\TextHelperService;
use XHRM\Core\Subscriber\ApiAuthorizationSubscriber;
use XHRM\Core\Subscriber\ExceptionSubscriber;
use XHRM\Core\Subscriber\GlobalConfigSubscriber;
use XHRM\Core\Subscriber\MailerSubscriber;
use XHRM\Core\Subscriber\ModuleNotAvailableSubscriber;
use XHRM\Core\Subscriber\RequestBodySubscriber;
use XHRM\Core\Subscriber\RequestForwardableExceptionSubscriber;
use XHRM\Core\Subscriber\ScreenAuthorizationSubscriber;
use XHRM\Core\Subscriber\SessionSubscriber;
use XHRM\Core\Traits\EventDispatcherTrait;
use XHRM\Core\Traits\Service\ConfigServiceTrait;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Console\Console;
use XHRM\Framework\Console\ConsoleConfigurationInterface;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\Session\MemorySessionStorage;
use XHRM\Framework\Http\Session\NativeSessionStorage;
use XHRM\Framework\Http\Session\Session;
use XHRM\Framework\PluginConfigurationInterface;
use XHRM\Framework\Services;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\EventListener\SessionListener;
use Symfony\Component\HttpKernel\KernelEvents;

class CorePluginConfiguration implements PluginConfigurationInterface, ConsoleConfigurationInterface
{
    use ServiceContainerTrait;
    use EventDispatcherTrait;
    use ConfigServiceTrait;

    /**
     * @inheritDoc
     */
    public function initialize(Request $request): void
    {
        $sessionStorage = $this->getSessionStorage($request);
        $session = new Session($sessionStorage);
        $session->start();

        $this->getContainer()->set(Services::SESSION_STORAGE, $sessionStorage);
        $this->getContainer()->set(Services::SESSION, $session);
        $this->getContainer()->register(Services::CONFIG_SERVICE, ConfigService::class);
        $this->getContainer()->register(Services::NORMALIZER_SERVICE, NormalizerService::class);
        $this->getContainer()->register(Services::DATETIME_HELPER_SERVICE, DateTimeHelperService::class);
        $this->getContainer()->register(Services::TEXT_HELPER_SERVICE, TextHelperService::class);
        $this->getContainer()->register(Services::NUMBER_HELPER_SERVICE, NumberHelperService::class);
        $this->getContainer()->register(Services::CLASS_HELPER, ClassHelper::class);
        $this->getContainer()->register(Services::USER_ROLE_MANAGER)
            ->setFactory([UserRoleManagerFactory::class, 'getUserRoleManager']);
        $this->getContainer()->register(Services::USER_ROLE_MANAGER_HELPER, UserRoleManagerHelper::class);
        $this->getContainer()->register(Services::CACHE)->setFactory([CacheService::class, 'getCache']);
        $this->getContainer()->register(Services::MENU_SERVICE, MenuService::class);
        $this->getContainer()->register(Services::MODULE_SERVICE, ModuleService::class);
        $this->getContainer()->register(Services::REPORT_GENERATOR_SERVICE, ReportGeneratorService::class);

        $this->registerCoreSubscribers();
    }

    private function registerCoreSubscribers(): void
    {
        $this->getEventDispatcher()->addSubscriber(new ExceptionSubscriber());
        $this->getEventDispatcher()->addListener(
            KernelEvents::REQUEST,
            [new SessionListener($this->getContainer()), 'onKernelRequest'],
        );
        $this->getEventDispatcher()->addSubscriber(new SessionSubscriber());
        $this->getEventDispatcher()->addSubscriber(new RequestForwardableExceptionSubscriber());
        $this->getEventDispatcher()->addSubscriber(new ScreenAuthorizationSubscriber());
        $this->getEventDispatcher()->addSubscriber(new ApiAuthorizationSubscriber());
        $this->getEventDispatcher()->addSubscriber(new RequestBodySubscriber());
        $this->getEventDispatcher()->addSubscriber(new MailerSubscriber());
        $this->getEventDispatcher()->addSubscriber(new ModuleNotAvailableSubscriber());
        $this->getEventDispatcher()->addSubscriber(new GlobalConfigSubscriber());
        if ($this->getConfigService()->getInstanceIdentifier() !== null) {
            $this->getEventDispatcher()->addSubscriber(new RegistrationEventPersistSubscriber());
        }
    }

    /**
     * @inheritDoc
     */
    public function registerCommands(Console $console): void
    {
        $console->add(new RunScheduleCommand());
        if (Config::PRODUCT_MODE !== Config::MODE_PROD) {
            $console->add(new EnableTestLanguagePackCommand());
        }
    }

    /**
     * @param Request $request
     * @return SessionStorageInterface
     */
    private function getSessionStorage(Request $request): SessionStorageInterface
    {
        if ($request->headers->has('authorization')) {
            // To reduce session IO operations, handle in-memory session storage for token based clients
            return new MemorySessionStorage();
        }
        $isSecure = $request->isSecure();
        $path = $request->getBasePath();
        $options = [
            'name' => $isSecure ? 'XHRM' : '_XHRM',
            'cookie_secure' => $isSecure,
            'cookie_httponly' => true,
            'cookie_path' => $path == '' ? '/' : $path,
            'cookie_samesite' => 'Lax',
        ];
        return new NativeSessionStorage(
            $options,
            new NativeFileSessionHandler(Config::get(Config::SESSION_DIR))
        );
    }
}

