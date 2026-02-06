<?php

namespace XHRM\PasswordManager\Api\Model;

use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\PasswordManager\Entity\VaultCategory;

/**
 * @OA\Schema(
 *     schema="PasswordManager-VaultCategoryModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="icon", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="userId", type="integer"),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 */
class VaultCategoryModel implements Normalizable
{
    use ModelTrait;

    public function __construct(VaultCategory $category)
    {
        $this->setEntity($category);
        $this->setFilters([
            'id',
            'name',
            'icon',
            'type',
            ['getUser', 'getId'],
            'createdAt',
            'updatedAt'
        ]);
        $this->setAttributeNames([
            'id',
            'name',
            'icon',
            'type',
            'userId',
            'createdAt',
            'updatedAt'
        ]);
    }
}
