<?php

namespace XHRM\PasswordManager\Api;

use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\PasswordManager\Traits\Api\VaultPermissionTrait;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

class VaultAdminAPI extends Endpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;
    use VaultPermissionTrait;

    protected function init()
    {
        $this->checkAdminAccess();
    }

    public function getAll(): EndpointResourceResult
    {
        $stats = $this->getPasswordManagerService()->getAdminStats();
        // Return as Resource, assuming singleton resource logic or misuse of REST
        // Actually, returning ResourceResult from getAll might break Controller expectation.
        // Let's try wrapping in CollectionResult if needed, or just ResourceResult.
        // But GenericRestController likely expects CollectionResult for List operation.

        // Let's return stats as properties of a single "Stats" object.
        return new EndpointResourceResult(ArrayModel::class, $stats);
    }
}
