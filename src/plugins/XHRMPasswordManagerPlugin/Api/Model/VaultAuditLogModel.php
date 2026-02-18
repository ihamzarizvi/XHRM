<?php

namespace XHRM\PasswordManager\Api\Model;

use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\PasswordManager\Entity\VaultAuditLog;

class VaultAuditLogModel implements Normalizable
{
    private VaultAuditLog $log;

    public function __construct(VaultAuditLog $log)
    {
        $this->log = $log;
    }

    public function toArray(): array
    {
        $item = $this->log->getVaultItem();
        return [
            'id' => $this->log->getId(),
            'userId' => $this->log->getUser()->getId(),
            'userName' => $this->log->getUser()->getUserName(),
            'userFullName' => trim(
                ($this->log->getUser()->getEmployee()?->getFirstName() ?? '') . ' ' .
                ($this->log->getUser()->getEmployee()?->getLastName() ?? '')
            ),
            'action' => $this->log->getAction(),
            'itemId' => $item ? $item->getId() : null,
            'itemName' => $item ? $item->getName() : null,
            'ipAddress' => $this->log->getIpAddress(),
            'userAgent' => $this->log->getUserAgent(),
            'createdAt' => $this->log->getCreatedAt()->format('c'),
        ];
    }
}
