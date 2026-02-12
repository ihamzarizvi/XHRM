<?php

namespace XHRM\PasswordManager\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\PasswordManager\Entity\VaultUserKey;

/**
 * @OA\Schema(
 *     schema="PasswordManager-VaultUserKeyModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="userId", type="integer"),
 *     @OA\Property(property="publicKey", type="string"),
 *     @OA\Property(property="encryptedPrivateKey", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 */
class VaultUserKeyModel implements Normalizable
{
    use ModelTrait;

    public function __construct(VaultUserKey $key)
    {
        $this->setEntity($key);
        $this->setFilters([
            'id',
            ['getUser', 'getId', true],
            'publicKey',
            'encryptedPrivateKey',
            'createdAt'
        ]);
        $this->setAttributeNames([
            'id',
            'userId',
            'publicKey',
            'encryptedPrivateKey',
            'createdAt'
        ]);
    }
}
