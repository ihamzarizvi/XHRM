<?php

namespace XHRM\PasswordManager\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use XHRM\Entity\User;

/**
 * @ORM\Table(name="ohrm_vault_item")
 * @ORM\Entity
 */
class VaultItem
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
     * @var VaultCategory|null
     * @ORM\ManyToOne(targetEntity="VaultCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?VaultCategory $category = null;

    /**
     * @var string
     * @ORM\Column(name="item_type", type="string", columnDefinition="ENUM('login', 'card', 'identity', 'note', 'totp')")
     */
    private string $itemType;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private string $name;

    /**
     * @var bool
     * @ORM\Column(name="favorite", type="boolean")
     */
    private bool $favorite = false;

    /**
     * @var string|null
     * @ORM\Column(name="username_encrypted", type="text", nullable=true)
     */
    private ?string $usernameEncrypted = null;

    /**
     * @var string|null
     * @ORM\Column(name="password_encrypted", type="text", nullable=true)
     */
    private ?string $passwordEncrypted = null;

    /**
     * @var string|null
     * @ORM\Column(name="url_encrypted", type="text", nullable=true)
     */
    private ?string $urlEncrypted = null;

    /**
     * @var string|null
     * @ORM\Column(name="notes_encrypted", type="text", nullable=true)
     */
    private ?string $notesEncrypted = null;

    /**
     * @var string|null
     * @ORM\Column(name="totp_secret_encrypted", type="text", nullable=true)
     */
    private ?string $totpSecretEncrypted = null;

    /**
     * @var string|null
     * @ORM\Column(name="custom_fields_encrypted", type="text", nullable=true)
     */
    private ?string $customFieldsEncrypted = null;

    /**
     * @var int|null
     * @ORM\Column(name="password_strength", type="integer", nullable=true)
     */
    private ?int $passwordStrength = null;

    /**
     * @var DateTime|null
     * @ORM\Column(name="password_last_changed", type="datetime", nullable=true)
     */
    private ?DateTime $passwordLastChanged = null;

    /**
     * @var bool
     * @ORM\Column(name="breach_detected", type="boolean")
     */
    private bool $breachDetected = false;

    /**
     * @var DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @var DateTime|null
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt = null;

    /**
     * @var DateTime|null
     * @ORM\Column(name="last_accessed", type="datetime", nullable=true)
     */
    private ?DateTime $lastAccessed = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters and Setters

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

    public function getCategory(): ?VaultCategory
    {
        return $this->category;
    }

    public function setCategory(?VaultCategory $category): void
    {
        $this->category = $category;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): void
    {
        $this->itemType = $itemType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    public function getUsernameEncrypted(): ?string
    {
        return $this->usernameEncrypted;
    }

    public function setUsernameEncrypted(?string $usernameEncrypted): void
    {
        $this->usernameEncrypted = $usernameEncrypted;
    }

    public function getPasswordEncrypted(): ?string
    {
        return $this->passwordEncrypted;
    }

    public function setPasswordEncrypted(?string $passwordEncrypted): void
    {
        $this->passwordEncrypted = $passwordEncrypted;
    }

    public function getUrlEncrypted(): ?string
    {
        return $this->urlEncrypted;
    }

    public function setUrlEncrypted(?string $urlEncrypted): void
    {
        $this->urlEncrypted = $urlEncrypted;
    }

    public function getNotesEncrypted(): ?string
    {
        return $this->notesEncrypted;
    }

    public function setNotesEncrypted(?string $notesEncrypted): void
    {
        $this->notesEncrypted = $notesEncrypted;
    }

    public function getTotpSecretEncrypted(): ?string
    {
        return $this->totpSecretEncrypted;
    }

    public function setTotpSecretEncrypted(?string $totpSecretEncrypted): void
    {
        $this->totpSecretEncrypted = $totpSecretEncrypted;
    }

    public function getCustomFieldsEncrypted(): ?string
    {
        return $this->customFieldsEncrypted;
    }

    public function setCustomFieldsEncrypted(?string $customFieldsEncrypted): void
    {
        $this->customFieldsEncrypted = $customFieldsEncrypted;
    }

    public function getPasswordStrength(): ?int
    {
        return $this->passwordStrength;
    }

    public function setPasswordStrength(?int $passwordStrength): void
    {
        $this->passwordStrength = $passwordStrength;
    }

    public function getPasswordLastChanged(): ?DateTime
    {
        return $this->passwordLastChanged;
    }

    public function setPasswordLastChanged(?DateTime $passwordLastChanged): void
    {
        $this->passwordLastChanged = $passwordLastChanged;
    }

    public function isBreachDetected(): bool
    {
        return $this->breachDetected;
    }

    public function setBreachDetected(bool $breachDetected): void
    {
        $this->breachDetected = $breachDetected;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getLastAccessed(): ?DateTime
    {
        return $this->lastAccessed;
    }

    public function setLastAccessed(?DateTime $lastAccessed): void
    {
        $this->lastAccessed = $lastAccessed;
    }
}
