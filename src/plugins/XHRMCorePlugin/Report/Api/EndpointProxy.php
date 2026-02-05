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

namespace XHRM\Core\Report\Api;

use XHRM\Core\Api\V2\Endpoint;
use XHRM\Core\Api\V2\Request;
use XHRM\Core\Api\V2\RequestParams;
use XHRM\Core\Api\V2\Validator\Helpers\ValidationDecorator;
use XHRM\Core\Dto\FilterParams;

class EndpointProxy extends Endpoint
{
    /**
     * @inheritDoc
     */
    public function getRequest(): Request
    {
        return parent::getRequest();
    }

    /**
     * @inheritDoc
     */
    public function getRequestParams(): RequestParams
    {
        return parent::getRequestParams();
    }

    /**
     * @inheritDoc
     */
    public function getValidationDecorator(): ValidationDecorator
    {
        return parent::getValidationDecorator();
    }

    /**
     * @inheritDoc
     */
    public function setSortingAndPaginationParams(
        FilterParams $searchParamHolder,
        ?string $defaultSortField = null
    ): FilterParams {
        return parent::setSortingAndPaginationParams($searchParamHolder, $defaultSortField);
    }

    /**
     * @inheritDoc
     */
    public function getSortingAndPaginationParamsRules(
        array $allowedSortFields = [],
        bool $excludeSortField = false
    ): array {
        return parent::getSortingAndPaginationParamsRules($allowedSortFields, $excludeSortField);
    }
}

