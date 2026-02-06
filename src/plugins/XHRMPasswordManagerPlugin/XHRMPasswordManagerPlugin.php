<?php

namespace XHRM\PasswordManager;

use XHRM\Core\Plugin\AbstractPlugin;
use XHRM\PasswordManager\DependencyInjection\PasswordManagerPluginConfiguration;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XHRMPasswordManagerPlugin extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
