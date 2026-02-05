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

namespace XHRM\Framework;

use Exception;
use XHRM\Authentication\Auth\AuthProviderChain;
use XHRM\Config\Config;
use XHRM\Core\Subscriber\LoggerSubscriber;
use XHRM\Framework\Event\EventDispatcher;
use XHRM\Framework\Http\ControllerResolver;
use XHRM\Framework\Http\Request;
use XHRM\Framework\Http\RequestStack;
use XHRM\Framework\Logger\Logger;
use XHRM\Framework\Logger\LoggerFactory;
use XHRM\Framework\Routing\RequestContext;
use XHRM\Framework\Routing\UrlGenerator;
use XHRM\Framework\Routing\UrlMatcher;
use XHRM\ORM\Doctrine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class Framework extends HttpKernel
{
    /**
     * @var null|string
     */
    protected ?string $environment = null;
    /**
     * @var null|bool
     */
    protected ?bool $debug = null;

    public function __construct(string $environment, bool $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->configureContainer();
        $this->configureLogger();

        /** @var EventDispatcher $dispatcher */
        $dispatcher = ServiceContainer::getContainer()->get(Services::EVENT_DISPATCHER);
        /** @var ControllerResolver $resolver */
        $resolver = ServiceContainer::getContainer()->get(Services::CONTROLLER_RESOLVER);
        /** @var RequestStack $requestStack */
        $requestStack = ServiceContainer::getContainer()->get(Services::REQUEST_STACK);
        /** @var ArgumentResolver $argumentResolver */
        $argumentResolver = ServiceContainer::getContainer()->get(Services::ARGUMENT_RESOLVER);

        parent::__construct($dispatcher, $resolver, $requestStack, $argumentResolver);
    }

    protected function configureContainer(): void
    {
        ServiceContainer::getContainer()->register(Services::REQUEST_STACK, RequestStack::class);
        ServiceContainer::getContainer()->register(Services::ROUTER_REQUEST_CONTEXT, RequestContext::class);
        ServiceContainer::getContainer()->register(Services::EVENT_DISPATCHER, EventDispatcher::class);
        ServiceContainer::getContainer()->register(Services::CONTROLLER_RESOLVER, ControllerResolver::class);
        ServiceContainer::getContainer()->register(Services::ARGUMENT_RESOLVER, ArgumentResolver::class);
        ServiceContainer::getContainer()->set(Services::HTTP_KERNEL, $this);
        ServiceContainer::getContainer()->register(Services::DOCTRINE)
            ->setFactory([Doctrine::class, 'getEntityManager']);
        ServiceContainer::getContainer()->register(Services::AUTH_PROVIDER_CHAIN, AuthProviderChain::class);
    }

    protected function configureLogger(): void
    {
        ServiceContainer::getContainer()->register(Services::LOGGER)
            ->setFactory([LoggerFactory::class, 'getLogger'])
            ->addArgument('xhrm');

        /** @var EventDispatcher $dispatcher */
        $dispatcher = ServiceContainer::getContainer()->get(Services::EVENT_DISPATCHER);
        $dispatcher->addSubscriber(new LoggerSubscriber());
    }

    protected function configureRouter(Request $request): void
    {
        /** @var RequestContext $context */
        $context = ServiceContainer::getContainer()->get(Services::ROUTER_REQUEST_CONTEXT);
        $context->fromRequest($request);

        $routes = RouteManager::getRoutes();
        ServiceContainer::getContainer()->register(Services::ROUTER, UrlMatcher::class)
            ->addArgument($routes)
            ->addArgument($context);

        /** @var UrlMatcher $matcher */
        $matcher = ServiceContainer::getContainer()->get(Services::ROUTER);
        /** @var RequestStack $requestStack */
        $requestStack = ServiceContainer::getContainer()->get(Services::REQUEST_STACK);

        /** @var Logger $logger */
        $logger = ServiceContainer::getContainer()->get(Services::LOGGER);

        $routerListener = new RouterListener($matcher, $requestStack, $context, $logger, null, $this->isDebug());
        /** @var EventDispatcher $dispatcher */
        $dispatcher = ServiceContainer::getContainer()->get(Services::EVENT_DISPATCHER);
        $dispatcher->addListener(KernelEvents::REQUEST, [$routerListener, 'onKernelRequest'], 99500);
        $dispatcher->addListener(KernelEvents::FINISH_REQUEST, [$routerListener, 'onKernelFinishRequest']);
        $dispatcher->addListener(KernelEvents::EXCEPTION, [$routerListener, 'onKernelException'], -64);

        $urlGenerator = new UrlGenerator($routes, $context, $logger);
        ServiceContainer::getContainer()->set(Services::URL_GENERATOR, $urlGenerator);

        $urlHelper = new UrlHelper($requestStack, $context);
        ServiceContainer::getContainer()->set(Services::URL_HELPER, $urlHelper);
    }

    protected function configurePlugins(Request $request): void
    {
        $pluginConfigs = Config::get(Config::PLUGIN_CONFIGS);
        foreach (array_values($pluginConfigs) as $pluginConfig) {
            require_once $pluginConfig['filepath'];
            /** @var PluginConfigurationInterface $configClass */
            $configClass = new $pluginConfig['classname']();
            $configClass->initialize($request);
        }
    }

    /**
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     * @throws Exception
     */
    public function handleRequest(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response {
        $this->configureRouter($request);
        $this->configurePlugins($request);

        return parent::handle($request, $type, $catch);
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
}

