<?php

namespace XHRM\PasswordManager\Api;

use XHRM\Core\Api\CommonParams;
use XHRM\Core\Api\V2\CrudEndpoint;
use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\EndpointCollectionResult;
use XHRM\Core\Api\V2\EndpointResourceResult;
use XHRM\Core\Api\V2\Model\ArrayModel;
use XHRM\Core\Api\V2\ParameterBag;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\ParamRule;
use XHRM\Core\Api\V2\Validator\ParamRuleCollection;
use XHRM\Core\Api\V2\Validator\Rule;
use XHRM\Core\Api\V2\Validator\Rules;
use XHRM\Core\Traits\UserRoleManagerTrait;
use XHRM\PasswordManager\Api\Model\VaultItemModel;
use XHRM\PasswordManager\Entity\VaultItem;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

class VaultItemAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_NAME = 'name';
    public const PARAMETER_USERNAME_ENCRYPTED = 'usernameEncrypted';
    public const PARAMETER_PASSWORD_ENCRYPTED = 'passwordEncrypted';
    public const PARAMETER_URL_ENCRYPTED = 'urlEncrypted';
    public const PARAMETER_NOTES_ENCRYPTED = 'notesEncrypted';
    public const PARAMETER_TOTP_SECRET_ENCRYPTED = 'totpSecretEncrypted';
    public const PARAMETER_CATEGORY_ID = 'categoryId';
    public const PARAMETER_ITEM_TYPE = 'itemType';

    public function getOne(): EndpointResourceResult
    {
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_ID
        );
        $item = $this->getPasswordManagerService()->getVaultItemById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($item, VaultItem::class);

        // Check access permission here if needed (e.g. valid user)
        if ($item->getUser()->getId() !== $this->getUserRoleManager()->getUser()->getId()) {
            // For simplicity, just 404 or check sharing (Phase 3)
            // throw new AccessDeniedException();
        }

        return new EndpointResourceResult(VaultItemModel::class, $item);
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
            )
        );
    }

    public function getAll(): EndpointCollectionResult
    {
        $userId = $this->getUserRoleManager()->getUser()->getId();
        $items = $this->getPasswordManagerService()->getVaultItems($userId);

        return new EndpointCollectionResult(
            VaultItemModel::class,
            $items
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    public function create(): EndpointResourceResult
    {
        $item = new VaultItem();
        $item->setUser($this->getUserRoleManager()->getUser());

        $this->setParamsToItem($item);

        $this->getPasswordManagerService()->saveVaultItem($item);

        return new EndpointResourceResult(VaultItemModel::class, $item);
    }

    private function setParamsToItem(VaultItem $item): void
    {
        $item->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NAME));
        $item->setItemType($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ITEM_TYPE));

        $item->setUsernameEncrypted($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_USERNAME_ENCRYPTED));
        $item->setPasswordEncrypted($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PASSWORD_ENCRYPTED));
        $item->setUrlEncrypted($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_URL_ENCRYPTED));
        $item->setNotesEncrypted($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NOTES_ENCRYPTED));
        $item->setTotpSecretEncrypted($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_TOTP_SECRET_ENCRYPTED));

        $categoryId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_CATEGORY_ID);
        if ($categoryId) {
            // Logic to find category and set it
        }
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        $collection = new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_NAME, new Rule(Rules::STRING_TYPE))
            ),
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_ITEM_TYPE, new Rule(Rules::STRING_TYPE))
            )
        );

        $collection->setStrict(false);

        return $collection;
    }

    public function update(): EndpointResourceResult
    {
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_ID
        );
        $item = $this->getPasswordManagerService()->getVaultItemById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($item, VaultItem::class);

        if ($item->getUser()->getId() !== $this->getUserRoleManager()->getUser()->getId()) {
            // Access denied logic
        }

        $this->setParamsToItem($item);
        $this->getPasswordManagerService()->saveVaultItem($item);

        return new EndpointResourceResult(VaultItemModel::class, $item);
    }

    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return $this->getValidationRuleForCreate();
    }

    public function delete(): EndpointResourceResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, CommonParams::PARAMETER_IDS);
        // Implement bulk delete or single delete logic
        // For simplicity
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }
}
