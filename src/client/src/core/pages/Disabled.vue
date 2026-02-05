<!--
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
 -->

<template>
  <div class="XHRM-background-container">
    <div class="XHRM-card-container">
      <div class="XHRM-module-disabled">
        <oxd-text tag="h1" class="XHRM-module-disabled-heading"> 403 </oxd-text>
        <oxd-text tag="h4" class="XHRM-module-disabled-sub-heading">
          {{ $t('general.module_forbidden') }}
        </oxd-text>
        <oxd-text tag="h6">
          {{ $t('general.module_access_restriction') }}
        </oxd-text>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.XHRM-module-disabled {
  text-align: center;
  margin-bottom: 1.2rem;
  &-heading {
    line-height: normal;
    color: $oxd-primary-one-color;
    font-weight: 800;
  }
  &-sub-heading {
    margin-top: -1.5rem;
    padding-bottom: 1rem;
    color: $oxd-primary-one-color;
    font-weight: 600;
  }
}
</style>
