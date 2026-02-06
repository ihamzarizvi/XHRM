<?php

namespace XHRM\PasswordManager\Traits\Service;

use XHRM\PasswordManager\Service\PasswordManagerService;

trait PasswordManagerServiceTrait
{
    /**
     * @var PasswordManagerService|null
     */
    protected ?PasswordManagerService $passwordManagerService = null;

    /**
     * @return PasswordManagerService
     */
    public function getPasswordManagerService(): PasswordManagerService
    {
        if (!$this->passwordManagerService instanceof PasswordManagerService) {
            $this->passwordManagerService = new PasswordManagerService();
        }
        return $this->passwordManagerService;
    }

    /**
     * @param PasswordManagerService $passwordManagerService
     */
    public function setPasswordManagerService(PasswordManagerService $passwordManagerService): void
    {
        $this->passwordManagerService = $passwordManagerService;
    }
}
