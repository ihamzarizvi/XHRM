<?php

namespace XHRM\PasswordManager\Config;

use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Http\Request;
use XHRM\Framework\PluginConfigurationInterface;

class PasswordManagerPluginConfiguration implements PluginConfigurationInterface
{
    use ServiceContainerTrait;

    /**
     * @inheritDoc
     */
    public function initialize(Request $request): void
    {
        // Initialize any specific services or parameters for PasswordManager here
    }
}
