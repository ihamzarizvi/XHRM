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
  <div class="XHRM-login-layout">
    <div class="XHRM-login-layout-blob">
      <div class="XHRM-login-container">
        <div class="XHRM-login-slot-wrapper">
          <login-branding :img-src="loginBannerSrc"></login-branding>
          <div class="XHRM-login-slot">
            <div class="XHRM-login-logo-mobile">
              <img :src="loginLogoSrc" alt="XHRM-logo" />
            </div>
            <slot></slot>
          </div>
        </div>
      </div>
      <!-- login logo hidden -->
      <!--
      <div class="XHRM-login-logo">
        <img :src="loginLogoSrc" alt="XHRM-logo" />
      </div>
      -->
    </div>
  </div>
</template>

<script>
import LoginBranding from './LoginBranding';

export default {
  name: 'LoginLayout',
  components: {
    'login-branding': LoginBranding,
  },
  props: {
    loginLogoSrc: {
      type: String,
      required: true,
    },
    loginBannerSrc: {
      type: String,
      required: true,
    },
  },
};
</script>

<style src="./login-layout.scss" lang="scss" scoped></style>
