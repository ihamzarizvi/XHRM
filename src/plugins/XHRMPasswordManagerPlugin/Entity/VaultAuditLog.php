<?php

namespace XHRM\PasswordManager\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\User;

/**
 * @ORM\Table(name="ohrm_vault_audit_log")
 * @ORM\Entity
 */
class VaultAuditLog
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @var VaultItem|null
     * @ORM\ManyToOne(targetEntity="VaultItem")
     * @ORM\JoinColumn(name="vault_item_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private ?VaultItem $vaultItem = null;

    /**
     * @var string
     * @ORM\Column(name="action", type="string", columnDefinition="ENUM('created', 'viewed', 'updated', 'deleted', 'password_copied', 'shared', 'unshared')")
     */
    private string $action;

    /**
     * @var string|null
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=true)
     */
    private ?string $ipAddress = null;

    /**
     * @var string|null
     * @ORM\Column(name="user_agent", type="text", nullable=true)
     */
    private ?string $userAgent = null;

    /**
     * @var DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getVaultItem(): ?VaultItem
    {
        return $this->vaultItem;
    }

    public function setVaultItem(?VaultItem $vaultItem): void
    {
        $this->vaultItem = $vaultItem;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
