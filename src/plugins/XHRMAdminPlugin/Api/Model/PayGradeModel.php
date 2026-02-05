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

namespace XHRM\Admin\Api\Model;

use OpenApi\Annotations as OA;
use XHRM\Core\Api\V2\Serializer\ModelTrait;
use XHRM\Core\Api\V2\Serializer\Normalizable;
use XHRM\Entity\CurrencyType;
use XHRM\Entity\PayGrade;
use XHRM\Entity\PayGradeCurrency;

/**
 * @OA\Schema(
 *     schema="Admin-PayGradeModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(
 *         property="currencies",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="string"),
 *             @OA\Property(property="name", type="string")
 *         )
*     )
 * )
 */
class PayGradeModel implements Normalizable
{
    use ModelTrait;

    public function __construct(PayGrade $payGrade)
    {
        $this->setEntity($payGrade);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $payGrade = $this->getEntity();
        $payGradeCurrencies = $payGrade->getPayGradeCurrencies();
        $currencies = [];
        foreach ($payGradeCurrencies as $payGradeCurrency) {
            $currency = [];
            if ($payGradeCurrency instanceof PayGradeCurrency) {
                $currencyType = $payGradeCurrency->getCurrencyType();
                if ($currencyType instanceof CurrencyType) {
                    $currency['name'] = $currencyType->getName();
                    $currency['id'] = $currencyType->getId();
                }
                $currencies[] = $currency;
            }
        }
        return [
           'id'     => $payGrade->getId(),
           'name'   => $payGrade->getName(),
           'currencies' => $currencies
       ];
    }
}

