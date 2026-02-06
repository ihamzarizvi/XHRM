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

namespace XHRM\ORM;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use XHRM\Config\Config;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Cache\FilesystemAdapter;
use XHRM\Framework\Framework;
use XHRM\Framework\Services;
use XHRM\ORM\Exception\ConfigNotFoundException;
use XHRM\ORM\Functions\TimeDiff;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Doctrine
{
    use ServiceContainerTrait;

    /**
     * @var null|Doctrine
     */
    protected static ?Doctrine $instance = null;
    /**
     * @var null|EntityManager
     */
    protected static ?EntityManager $entityManager = null;

    /**
     * @throws ConfigNotFoundException
     */
    private function __construct()
    {
        $conf = Config::getConf();

        $isDevMode = $this->isDevMode();
        $proxyDir = Config::get(Config::DOCTRINE_PROXY_DIR);
        if (!$proxyDir || !is_dir($proxyDir)) {
            $proxyDir = realpath(Config::get(Config::SRC_DIR) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'proxy');
        }
        $cache = new ArrayAdapter();
        $paths = $this->getPaths();
        $config = ORMSetup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            $proxyDir,
            $cache
        );
        if (!$isDevMode) {
            $metadataCache = new FilesystemAdapter('doctrine_metadata', 0, Config::get(Config::CACHE_DIR));
            $queryCache = new FilesystemAdapter('doctrine_queries', 0, Config::get(Config::CACHE_DIR));
            $config->setMetadataCache($metadataCache);
            $config->setQueryCache($queryCache);
        }

        $config->setAutoGenerateProxyClasses(
            $isDevMode
            ? AbstractProxyFactory::AUTOGENERATE_ALWAYS
            : AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS
        );
        $config->addCustomStringFunction('TIME_DIFF', TimeDiff::class);

        $connectionParams = [
            'dbname' => $conf->getDbName(),
            'user' => $conf->getDbUser(),
            'password' => $conf->getDbPass(),
            'host' => $conf->getDbHost(),
            'port' => $conf->getDbPort(),
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4'
        ];

        self::$entityManager = EntityManager::create($connectionParams, $config);
        self::$entityManager->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * @return bool
     */
    private function isDevMode(): bool
    {
        try {
            /** @var Framework $kernel */
            $kernel = $this->getContainer()->get(Services::HTTP_KERNEL);
            return $kernel->isDebug();
        } catch (ServiceNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    private function getPaths(): array
    {
        $paths = [];
        $pluginPaths = Config::get('ohrm_plugin_paths');
        foreach ($pluginPaths as $pluginPath) {
            $entityPath = realpath($pluginPath . '/entity');
            if ($entityPath) {
                $paths[] = $entityPath;
            }
        }
        return $paths;
    }

    /**
     * @return Doctrine
     */
    protected static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager(): EntityManager
    {
        self::getInstance();
        return self::$entityManager;
    }
}

