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

namespace XHRM\LDAP\Dto;

use Symfony\Component\Ldap\Adapter\CollectionInterface;

class EntryCollectionLookupSettingPair
{
    private CollectionInterface $collection;
    private LDAPUserLookupSetting $lookupSetting;

    /**
     * @param CollectionInterface $collection
     * @param LDAPUserLookupSetting $lookupSetting
     */
    public function __construct(CollectionInterface $collection, LDAPUserLookupSetting $lookupSetting)
    {
        $this->collection = $collection;
        $this->lookupSetting = $lookupSetting;
    }

    /**
     * @return CollectionInterface
     */
    public function getCollection(): CollectionInterface
    {
        return $this->collection;
    }

    /**
     * @return LDAPUserLookupSetting
     */
    public function getLookupSetting(): LDAPUserLookupSetting
    {
        return $this->lookupSetting;
    }
}

