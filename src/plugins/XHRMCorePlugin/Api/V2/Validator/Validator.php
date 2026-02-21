<?php

/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace XHRM\Core\Api\V2\Validator;

use Exception;
use XHRM\Core\Api\V2\Exception\InvalidParamException;
use XHRM\Core\Api\V2\Validator\Exceptions\ValidationEscapableException;
use XHRM\Core\Api\V2\Validator\Exceptions\ValidationException;
use Respect\Validation\Rules;

class Validator
{
    /**
     * @param array $values
     * @param ParamRuleCollection|null $rules
     * @return bool
     * @throws InvalidParamException
     */
    public static function validate(array $values, ?ParamRuleCollection $rules = null): bool
    {
        // TEMPORARY DEBUG - captures exactly what triggers 422
        $debugUri = $_SERVER['REQUEST_URI'] ?? 'cli';
        if (strpos($debugUri, 'payroll') !== false || strpos($debugUri, 'salary') !== false || strpos($debugUri, 'holiday') !== false) {
            $debugLogFile = realpath(__DIR__ . '/../../../../../../') . '/web/api_debug_log.json';
            $debugEntry = [
                'time' => date('Y-m-d H:i:s'),
                'uri' => $debugUri,
                'param_values' => $values,
                'rule_keys' => $rules ? array_keys($rules->getMap()) : [],
                'is_strict' => $rules ? $rules->isStrict() : null,
            ];
            $debugExisting = [];
            if (file_exists($debugLogFile)) {
                $debugExisting = json_decode(file_get_contents($debugLogFile), true) ?: [];
            }
            $debugExisting[] = $debugEntry;
            $debugExisting = array_slice($debugExisting, -20);
            @file_put_contents($debugLogFile, json_encode($debugExisting, JSON_PRETTY_PRINT));
        }
        // END TEMPORARY DEBUG

        $paramRules = $rules->getMap();
        $paramKeys = array_keys($paramRules);
        $values = self::getOnlyNecessaryValues($values, $rules);

        if ($rules->isStrict()) {
            $paramKeys = array_unique(array_merge($paramKeys, array_keys($values)));
        }

        $errorBag = [];
        foreach ($paramKeys as $paramKey) {
            try {
                if (isset($paramRules[$paramKey])) {
                    $paramRule = $paramRules[$paramKey];

                    $compositeClass = $paramRule->getCompositeClass();
                    $paramValidatorRule = new $compositeClass(...$paramRule->getRules());
                    $paramValidator = new Rules\Key($paramKey, $paramValidatorRule);
                    $paramValidator->check(
                        [$paramKey => $values[$paramKey] ?? $paramRule->getDefault()]
                    );
                } else {
                    throw new InvalidParamException(
                        [],
                        sprintf('Unexpected Parameter (`%s`) Received', $paramKey)
                    );
                }
            } catch (ValidationException | Exception $e) {
                if ($e instanceof ValidationEscapableException) {
                    throw $e;
                }
                $errorBag[$paramKey] = $e;
            }
        }
        if (!empty($errorBag)) {
            throw new InvalidParamException($errorBag);
        }

        return true;
    }

    /**
     * @param array $values
     * @param ParamRuleCollection|null $rules
     * @return array
     */
    private static function getOnlyNecessaryValues(array $values, ?ParamRuleCollection $rules = null): array
    {
        $excludedParamKeys = is_null($rules) ?
            ParamRuleCollection::DEFAULT_EXCLUDED_PARAM_KEYS :
            $rules->getExcludedParamKeys();
        foreach ($excludedParamKeys as $excludedParamKey) {
            unset($values[$excludedParamKey]);
        }
        return $values;
    }
}

