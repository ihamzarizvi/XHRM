<?php

namespace XHRM\PasswordManager\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\PasswordManager\Entity\VaultShare;

/**
 * @OA\Schema(
 *     schema="PasswordManager-VaultShareModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="vaultItemId", type="integer"),
 *     @OA\Property(property="sharedWithUserId", type="integer"),
 *     @OA\Property(property="sharedByUserId", type="integer"),
 *     @OA\Property(property="permission", type="string"),
 *     @OA\Property(property="encryptedKeyForRecipient", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 */
class VaultShareModel implements Normalizable
{
    use ModelTrait;

    public function __construct(VaultShare $share)
    {
        $this->setEntity($share);
        $this->setFilters([
            'id',
            ['getVaultItem', 'getId', true],
            ['getSharedWithUser', 'getId', true],
            ['getSharedByUser', 'getId', true],
            'permission',
            'encryptedKeyForRecipient',
            'createdAt'
        ]);
        $this->setAttributeNames([
            'id',
            'vaultItemId',
            'sharedWithUserId',
            'sharedByUserId',
            'permission',
            'encryptedKeyForRecipient',
            'createdAt'
        ]);
    }
}
