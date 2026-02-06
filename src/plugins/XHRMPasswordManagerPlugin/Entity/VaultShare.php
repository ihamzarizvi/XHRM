<?php

namespace XHRM\PasswordManager\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\User;

/**
 * @ORM\Table(name="ohrm_vault_share")
 * @ORM\Entity
 */
class VaultShare
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var VaultItem
     * @ORM\ManyToOne(targetEntity="VaultItem")
     * @ORM\JoinColumn(name="vault_item_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private VaultItem $vaultItem;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\User")
     * @ORM\JoinColumn(name="shared_by_user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private User $sharedByUser;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="XHRM\Entity\User")
     * @ORM\JoinColumn(name="shared_with_user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private User $sharedWithUser;

    /**
     * @var string
     * @ORM\Column(name="permission", type="string", columnDefinition="ENUM('read', 'write', 'admin')")
     */
    private string $permission = 'read';

    /**
     * @var string|null
     * @ORM\Column(name="encrypted_key_for_recipient", type="text", nullable=true)
     */
    private ?string $encryptedKeyForRecipient = null;

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

    public function getVaultItem(): VaultItem
    {
        return $this->vaultItem;
    }

    public function setVaultItem(VaultItem $vaultItem): void
    {
        $this->vaultItem = $vaultItem;
    }

    public function getSharedByUser(): User
    {
        return $this->sharedByUser;
    }

    public function setSharedByUser(User $sharedByUser): void
    {
        $this->sharedByUser = $sharedByUser;
    }

    public function getSharedWithUser(): User
    {
        return $this->sharedWithUser;
    }

    public function setSharedWithUser(User $sharedWithUser): void
    {
        $this->sharedWithUser = $sharedWithUser;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    public function getEncryptedKeyForRecipient(): ?string
    {
        return $this->encryptedKeyForRecipient;
    }

    public function setEncryptedKeyForRecipient(?string $encryptedKeyForRecipient): void
    {
        $this->encryptedKeyForRecipient = $encryptedKeyForRecipient;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
