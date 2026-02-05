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
      <div class="XHRM-module-under-development">
        <oxd-text tag="h4" class="XHRM-module-under-development-heading">
          {{ $t('general.launching_soon') }}
        </oxd-text>
        <img
          :src="moduleUnderDevelopment"
          class="XHRM-module-under-development-img"
          alt="this module is under development"
        />
        <oxd-text tag="p" class="XHRM-module-under-development-text">
          {{ $t('general.page_under_development') }}
          <a
            target="_blank"
            class="XHRM-module-under-development-link"
            href="https://sourceforge.net/projects/XHRM/files/latest/download"
          >
            {{ $t('general.click_here') }}
          </a>
          {{ $t('general.download_latest_release') }}
        </oxd-text>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      moduleUnderDevelopment: `${window.appGlobal.publicPath}/images/web_under_construction.png`,
    };
  },
};
</script>

<style lang="scss" scoped>
.XHRM-module-under-development {
  text-align: center;
  &-img {
    max-width: 100%;
    height: 100%;
    max-height: 350px;
    display: block;
    margin: 0 auto;
    object-fit: fill;
  }
  &-heading {
    color: $oxd-primary-one-color;
    font-weight: 700;
  }
  &-text {
    font-size: 14px;
    max-width: 800px;
    margin: 0 auto;
    margin-bottom: 2rem;
  }
  &-link {
    text-decoration: none;
    color: $oxd-primary-one-color;
  }
}
</style>

