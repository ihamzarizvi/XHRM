<?php

namespace XHRM\PasswordManager\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\PasswordManager\Entity\VaultItem;

/**
 * @OA\Schema(
 *     schema="PasswordManager-VaultItemModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="itemType", type="string"),
 *     @OA\Property(property="favorite", type="boolean"),
 *     @OA\Property(property="categoryId", type="integer", nullable=true),
 *     @OA\Property(property="usernameEncrypted", type="string"),
 *     @OA\Property(property="passwordEncrypted", type="string"),
 *     @OA\Property(property="urlEncrypted", type="string"),
 *     @OA\Property(property="notesEncrypted", type="string"),
 *     @OA\Property(property="totpSecretEncrypted", type="string"),
 *     @OA\Property(property="customFieldsEncrypted", type="string"),
 *     @OA\Property(property="passwordStrength", type="integer", nullable=true),
 *     @OA\Property(property="breachDetected", type="boolean"),
 *     @OA\Property(property="lastAccessed", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class VaultItemModel implements Normalizable
{
    use ModelTrait;

    public function __construct(VaultItem $item)
    {
        $this->setEntity($item);
        $this->setFilters([
            'id',
            'name',
            'itemType',
            'favorite',
            ['getCategory', 'getId', true],
            'usernameEncrypted',
            'passwordEncrypted',
            'urlEncrypted',
            'notesEncrypted',
            'totpSecretEncrypted',
            'customFieldsEncrypted',
            'passwordStrength',
            'breachDetected',
            'lastAccessed',
            'createdAt',
            'updatedAt'
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'itemType',
            'favorite',
            'categoryId',
            'usernameEncrypted',
            'passwordEncrypted',
            'urlEncrypted',
            'notesEncrypted',
            'totpSecretEncrypted',
            'customFieldsEncrypted',
            'passwordStrength',
            'breachDetected',
            'lastAccessed',
            'createdAt',
            'updatedAt'
        ]);
    }
}
