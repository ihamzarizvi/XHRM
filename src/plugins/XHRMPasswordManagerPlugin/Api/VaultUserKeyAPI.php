<?php

namespace XHRM\PasswordManager\Api;

use XHRM\Core\Api\V2\CrudEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\PasswordManager\Api\Model\VaultUserKeyModel;
use XHRM\PasswordManager\Entity\VaultUserKey;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;
use XHRM\PasswordManager\Traits\Api\VaultPermissionTrait;

class VaultUserKeyAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;
    use VaultPermissionTrait;

    protected function init()
    {
        $this->checkVaultAccess();
    }

    public const PARAMETER_USER_ID = 'userId';
    public const PARAMETER_PUBLIC_KEY = 'publicKey';
    public const PARAMETER_ENCRYPTED_PRIVATE_KEY = 'encryptedPrivateKey';

    public function getOne(): EndpointResourceResult
    {
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    public function getAll(): EndpointCollectionResult
    {
        $userIdParam = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_USER_ID);
        $currentUser = $this->getUserRoleManager()->getUser();

        if ($userIdParam === 'me') {
            $userId = $currentUser->getId();
        } else {
            $userId = (int) $userIdParam;
        }

        if (!$userId) {
            return new EndpointCollectionResult(VaultUserKeyModel::class, []);
        }

        $key = $this->getPasswordManagerService()->getVaultUserKeyDao()->findByUserId($userId);

        if (!$key) {
            return new EndpointCollectionResult(VaultUserKeyModel::class, []);
        }

        // Security: Strip Encrypted Private Key if not current user
        if ($key->getUser()->getId() !== $currentUser->getId()) {
            $keyClone = clone $key;
            $keyClone->setEncryptedPrivateKey('');
            $key = $keyClone;
        }

        return new EndpointCollectionResult(VaultUserKeyModel::class, [$key]);
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_USER_ID, new Rule(Rules::STRING_TYPE))
        );
    }

    public function create(): EndpointResourceResult
    {
        $publicKey = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PUBLIC_KEY);
        $encryptedPrivateKey = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ENCRYPTED_PRIVATE_KEY);

        $user = $this->getUserRoleManager()->getUser();

        // Upsert logic
        $key = $this->getPasswordManagerService()->getVaultUserKeyDao()->findByUserId($user->getId());

        if (!$key) {
            $key = new VaultUserKey();
            $key->setUser($user);
        }

        $key->setPublicKey($publicKey);
        $key->setEncryptedPrivateKey($encryptedPrivateKey);

        $this->getPasswordManagerService()->getVaultUserKeyDao()->save($key);

        return new EndpointResourceResult(VaultUserKeyModel::class, $key);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_PUBLIC_KEY, new Rule(Rules::STRING_TYPE))
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_ENCRYPTED_PRIVATE_KEY, new Rule(Rules::STRING_TYPE))
            )
        );
    }

    public function update(): EndpointResourceResult
    {
        return new EndpointResourceResult(ArrayModel::class, []);
    }
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }
    public function delete(): EndpointResourceResult
    {
        return new EndpointResourceResult(ArrayModel::class, []);
    }
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }
}
