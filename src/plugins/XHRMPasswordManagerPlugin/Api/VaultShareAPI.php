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
use XHRM\PasswordManager\Api\Model\VaultShareModel;
use XHRM\PasswordManager\Entity\VaultShare;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

class VaultShareAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_VAULT_ITEM_ID = 'vaultItemId';
    public const PARAMETER_SHARED_WITH_USER_ID = 'sharedWithUserId';
    public const PARAMETER_PERMISSION = 'permission';

    public function getOne(): EndpointResourceResult
    {
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_ID
        );
        $share = $this->getPasswordManagerService()->getVaultShareDao()->find($id);
        $this->throwRecordNotFoundExceptionIfNotExist($share, VaultShare::class);

        return new EndpointResourceResult(VaultShareModel::class, $share);
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE))
        );
    }

    public function getAll(): EndpointCollectionResult
    {
        // Typically list shares for a specific item, or shares involving the user
        // For simplicity, returning empty or could implement "getSharesByItem" logic
        return new EndpointCollectionResult(VaultShareModel::class, []);
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    public function create(): EndpointResourceResult
    {
        $vaultItemId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_VAULT_ITEM_ID);
        $sharedWithUserId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SHARED_WITH_USER_ID);
        $permission = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PERMISSION);

        $item = $this->getPasswordManagerService()->getVaultItemDao()->find($vaultItemId);
        // Validate item ownership...

        $share = new VaultShare();
        $share->setVaultItem($item);
        $share->setSharedByUser($this->getUserRoleManager()->getUser());
        // Set sharedWithUser logic (requires User DAO retrieval)
        // $share->setSharedWithUser(...)
        $share->setPermission($permission);

        $this->getPasswordManagerService()->getVaultShareDao()->save($share);

        return new EndpointResourceResult(VaultShareModel::class, $share);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_VAULT_ITEM_ID, new Rule(Rules::POSITIVE))
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_SHARED_WITH_USER_ID, new Rule(Rules::POSITIVE))
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_PERMISSION, new Rule(Rules::STRING_TYPE))
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
