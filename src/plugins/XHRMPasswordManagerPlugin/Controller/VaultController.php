<?php

namespace XHRM\PasswordManager\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Vue\Component;
use XHRM\Framework\Http\Request;

class VaultController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('password-manager-layout');
        // Add props if needed, e.g. permissions
        $this->setComponent($component);
    }
}
