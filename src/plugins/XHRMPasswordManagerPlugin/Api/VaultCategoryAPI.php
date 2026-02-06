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
use XHRM\PasswordManager\Api\Model\VaultCategoryModel;
use XHRM\PasswordManager\Entity\VaultCategory;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

class VaultCategoryAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

    public const PARAMETER_ID = 'id';
    public const PARAMETER_NAME = 'name';
    public const PARAMETER_ICON = 'icon';

    public function getOne(): EndpointResourceResult
    {
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_ID
        );
        $category = $this->getPasswordManagerService()->getVaultCategoryDao()->find($id);
        $this->throwRecordNotFoundExceptionIfNotExist($category, VaultCategory::class);

        return new EndpointResourceResult(VaultCategoryModel::class, $category);
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ID, new Rule(Rules::POSITIVE))
        );
    }

    public function getAll(): EndpointCollectionResult
    {
        $userId = $this->getUserRoleManager()->getUser()->getId();
        $categories = $this->getPasswordManagerService()->getCategories($userId);
        return new EndpointCollectionResult(VaultCategoryModel::class, $categories);
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    public function create(): EndpointResourceResult
    {
        $category = new VaultCategory();
        $category->setUser($this->getUserRoleManager()->getUser());
        $category->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NAME));
        $category->setIcon($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ICON));

        $this->getPasswordManagerService()->saveCategory($category);

        return new EndpointResourceResult(VaultCategoryModel::class, $category);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_NAME, new Rule(Rules::STRING_TYPE))
            )
        );
    }

    public function update(): EndpointResourceResult
    {
        // Implement update
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    public function delete(): EndpointResourceResult
    {
        // Implement delete
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }
}
