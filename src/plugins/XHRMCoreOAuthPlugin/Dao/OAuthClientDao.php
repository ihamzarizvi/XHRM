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

namespace XHRM\OAuth\Dao;

use XHRM\Core\Dao\BaseDao;
use XHRM\Entity\OAuthClient;
use XHRM\OAuth\Dto\OAuthClientSearchFilterParams;
use XHRM\ORM\QueryBuilderWrapper;

class OAuthClientDao extends BaseDao
{
    /**
     * @param OAuthClient $oauthClient
     * @return OAuthClient
     */
    public function saveOAuthClient(OAuthClient $oauthClient): OAuthClient
    {
        $this->persist($oauthClient);
        return $oauthClient;
    }

    /**
     * @param OAuthClientSearchFilterParams $oauthClientSearchFilterParams
     * @return OAuthClient[]
     */
    public function getOAuthClientList(OAuthClientSearchFilterParams $oauthClientSearchFilterParams): array
    {
        $qb = $this->getOAuthClientQueryBuilderWrapper($oauthClientSearchFilterParams)->getQueryBuilder();
        return $qb->getQuery()->execute();
    }

    /**
     * @param OAuthClientSearchFilterParams $oauthClientSearchFilterParams
     * @return int
     */
    public function getOAuthClientCount(OAuthClientSearchFilterParams $oauthClientSearchFilterParams): int
    {
        $qb = $this->getOAuthClientQueryBuilderWrapper($oauthClientSearchFilterParams)->getQueryBuilder();
        return $this->getPaginator($qb)->count();
    }

    /**
     * @param OAuthClientSearchFilterParams $oauthClientSearchFilterParams
     * @return QueryBuilderWrapper
     */
    private function getOAuthClientQueryBuilderWrapper(OAuthClientSearchFilterParams $oauthClientSearchFilterParams): QueryBuilderWrapper
    {
        $q = $this->createQueryBuilder(OAuthClient::class, 'oauthClient');
        $this->setSortingAndPaginationParams($q, $oauthClientSearchFilterParams);
        return $this->getQueryBuilderWrapper($q);
    }

    /**
     * @param int $id
     * @return OAuthClient|null
     */
    public function getOAuthClientById(int $id): ?OAuthClient
    {
        return $this->getRepository(OAuthClient::class)->find($id);
    }

    /**
     * @param string $clientId
     * @return OAuthClient|null
     */
    public function getOAuthClientByClientId(string $clientId): ?OAuthClient
    {
        return $this->getRepository(OAuthClient::class)->findOneBy(['clientId' => $clientId]);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function deleteOAuthClients(array $ids): int
    {
        $q = $this->createQueryBuilder(OAuthClient::class, 'oauthClient');
        $q->delete()
            ->where($q->expr()->in('oauthClient.id', ':ids'))
            ->setParameter('ids', $ids);
        return $q->getQuery()->execute();
    }
}

