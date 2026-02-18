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
use XHRM\PasswordManager\Api\Model\VaultAuditLogModel;
use XHRM\PasswordManager\Entity\VaultAuditLog;
use XHRM\PasswordManager\Traits\Service\PasswordManagerServiceTrait;

/**
 * Audit Log API — admin-only endpoint to read activity logs.
 * Also handles client-side events (password_copied, url_launched) via POST.
 *
 * Routes:
 *   GET  /api/v2/password-manager/audit-logs         → getAll() (admin)
 *   POST /api/v2/password-manager/audit-logs         → create() (any user, logs client event)
 */
class VaultAuditLogAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

    public const PARAMETER_ACTION = 'action';
    public const PARAMETER_ITEM_ID = 'vaultItemId';
    public const PARAMETER_USER_ID = 'userId';
    public const PARAMETER_FROM = 'from';
    public const PARAMETER_TO = 'to';
    public const PARAMETER_LIMIT = 'limit';
    public const PARAMETER_OFFSET = 'offset';

    // Client-side actions that users can self-report
    private const CLIENT_ACTIONS = ['password_copied', 'url_launched'];

    public function getOne(): EndpointResourceResult
    {
        return new EndpointResourceResult(ArrayModel::class, []);
    }

    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    /**
     * GET /api/v2/password-manager/audit-logs
     * Returns paginated audit log. Admin-only.
     */
    public function getAll(): EndpointCollectionResult
    {
        $filters = [];

        $userId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_USER_ID);
        if ($userId) {
            $filters['userId'] = $userId;
        }

        $action = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_ACTION);
        if ($action) {
            $filters['action'] = $action;
        }

        $from = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_FROM);
        if ($from) {
            $filters['from'] = $from;
        }

        $to = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_TO);
        if ($to) {
            $filters['to'] = $to;
        }

        $limit = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_LIMIT) ?? 50;
        $offset = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_OFFSET) ?? 0;

        $logs = $this->getPasswordManagerService()->getVaultAuditLogDao()->getAuditLogs($filters, $limit, $offset);
        $total = $this->getPasswordManagerService()->getVaultAuditLogDao()->countAuditLogs($filters);

        // Use ArrayModel since VaultAuditLogModel doesn't use ModelTrait
        $data = array_map(fn($log) => (new VaultAuditLogModel($log))->toArray(), $logs);

        return new EndpointCollectionResult(
            ArrayModel::class,
            $data,
            new \XHRM\Core\Api\V2\ParameterBag([
                'total' => $total,
            ])
        );
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_USER_ID, new Rule(Rules::INT_TYPE)),
            new ParamRule(self::PARAMETER_ACTION, new Rule(Rules::STRING_TYPE)),
            new ParamRule(self::PARAMETER_FROM, new Rule(Rules::STRING_TYPE)),
            new ParamRule(self::PARAMETER_TO, new Rule(Rules::STRING_TYPE)),
            new ParamRule(self::PARAMETER_LIMIT, new Rule(Rules::INT_TYPE)),
            new ParamRule(self::PARAMETER_OFFSET, new Rule(Rules::INT_TYPE))
        );
    }

    /**
     * POST /api/v2/password-manager/audit-logs
     * Client reports a user action (password_copied, url_launched).
     */
    public function create(): EndpointResourceResult
    {
        $action = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ACTION);
        $vaultItemId = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ITEM_ID);

        // Only allow safe client-reported actions
        if (!in_array($action, self::CLIENT_ACTIONS, true)) {
            throw new \InvalidArgumentException("Invalid action: $action");
        }

        $currentUser = $this->getUserRoleManager()->getUser();
        $item = null;
        if ($vaultItemId) {
            $item = $this->getPasswordManagerService()->getVaultItemById($vaultItemId);
        }

        $this->getPasswordManagerService()->logAuditEvent($currentUser, $action, $item);

        return new EndpointResourceResult(ArrayModel::class, ['logged' => true]);
    }

    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                new ParamRule(self::PARAMETER_ACTION, new Rule(Rules::STRING_TYPE))
            ),
            new ParamRule(self::PARAMETER_ITEM_ID, new Rule(Rules::INT_TYPE))
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
