<?php

namespace XHRM\PasswordManager\Api;

use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

class VaultAdminAPI extends Endpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

    public function getAll(): EndpointResourceResult
    {
        $stats = $this->getPasswordManagerService()->getAdminStats();
        return new EndpointResourceResult(ArrayModel::class, $stats);
    }
}
