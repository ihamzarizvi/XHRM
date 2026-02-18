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

/**
 * VaultUserKeyAPI — manages per-user encryption salt for auto-unlock.
 *
 * GET  /api/v2/password-manager/user-keys
 *   Returns the current user's salt (and RSA public key for sharing).
 *   If no key row exists, generates a random salt + RSA key pair on the fly.
 *   The frontend uses the salt to derive the AES master key via PBKDF2.
 *
 * POST /api/v2/password-manager/user-keys
 *   Stores the RSA key pair (public key + encrypted private key).
 *   The encrypted private key is encrypted client-side with the derived AES master key.
 *
 * GET  /api/v2/password-manager/user-keys?userId=<id>
 *   Returns another user's public key (for sharing). Strips private key.
 */
class VaultUserKeyAPI extends Endpoint implements CrudEndpoint
{
    use PasswordManagerServiceTrait;
    use UserRoleManagerTrait;

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

    /**
     * GET /api/v2/password-manager/user-keys
     * GET /api/v2/password-manager/user-keys?userId=me
     * GET /api/v2/password-manager/user-keys?userId=<id>   (returns public key only)
     */
    public function getAll(): EndpointCollectionResult
    {
        $userIdParam = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_USER_ID);
        $currentUser = $this->getUserRoleManager()->getUser();

        // Determine which user's key to fetch
        if ($userIdParam === null || $userIdParam === 'me') {
            $userId = $currentUser->getId();
        } else {
            $userId = (int) $userIdParam;
        }

        $isOwnKey = ($userId === $currentUser->getId());

        $key = $this->getPasswordManagerService()->getVaultUserKeyDao()->findByUserId($userId);

        // Auto-provision: if current user has no key row, create one with a random salt
        if (!$key && $isOwnKey) {
            $key = new VaultUserKey();
            $key->setUser($currentUser);
            // Generate a cryptographically random 32-byte salt stored as hex
            $salt = bin2hex(random_bytes(32));
            $key->setPublicKey($salt);           // Repurpose public_key column as salt storage
            $key->setEncryptedPrivateKey('');    // Will be filled after client generates RSA pair
            $this->getPasswordManagerService()->getVaultUserKeyDao()->save($key);
        }

        if (!$key) {
            return new EndpointCollectionResult(VaultUserKeyModel::class, []);
        }

        // Strip encrypted private key when fetching another user's key
        if (!$isOwnKey) {
            $keyClone = clone $key;
            $keyClone->setEncryptedPrivateKey('');
            $key = $keyClone;
        }

        return new EndpointCollectionResult(VaultUserKeyModel::class, [$key]);
    }

    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        // No required params — userId is optional and handled safely in getAll()
        return new ParamRuleCollection();
    }

    /**
     * POST /api/v2/password-manager/user-keys
     * Stores RSA public key + encrypted private key after client generates them.
     */
    public function create(): EndpointResourceResult
    {
        $publicKey = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PUBLIC_KEY);
        $encryptedPrivateKey = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ENCRYPTED_PRIVATE_KEY);

        $user = $this->getUserRoleManager()->getUser();

        $key = $this->getPasswordManagerService()->getVaultUserKeyDao()->findByUserId($user->getId());

        if (!$key) {
            $key = new VaultUserKey();
            $key->setUser($user);
            // Generate salt if missing
            $key->setPublicKey(bin2hex(random_bytes(32)));
        }

        // Store RSA public key in a separate field — we need to keep the salt.
        // Strategy: store as JSON: {"salt":"hex...","rsaPublicKey":"..."}
        $existing = $key->getPublicKey();
        $decoded = json_decode($existing, true);

        if (is_array($decoded) && isset($decoded['salt'])) {
            // Already has JSON format, update RSA key
            $decoded['rsaPublicKey'] = $publicKey;
        } else {
            // Plain salt (first RSA key upload), convert to JSON
            $decoded = [
                'salt' => $existing ?: bin2hex(random_bytes(32)),
                'rsaPublicKey' => $publicKey,
            ];
        }

        $key->setPublicKey(json_encode($decoded));
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
