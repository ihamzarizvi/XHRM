<?php

namespace XHRM\PasswordManager\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\User;

/**
 * @ORM\Table(name="ohrm_vault_user_key")
 * @ORM\Entity
 */
class VaultUserKey
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
     * @ORM\OneToOne(targetEntity="XHRM\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @var string
     * @ORM\Column(name="public_key", type="text")
     */
    private string $publicKey;

    /**
     * @var string
     * @ORM\Column(name="encrypted_private_key", type="text")
     */
    private string $encryptedPrivateKey;

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

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getEncryptedPrivateKey(): string
    {
        return $this->encryptedPrivateKey;
    }

    public function setEncryptedPrivateKey(string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
