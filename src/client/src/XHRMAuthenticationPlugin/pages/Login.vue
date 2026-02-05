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
  <login-layout>
    <oxd-text class="XHRM-login-title" tag="h5">
      {{ $t('auth.login') }}
    </oxd-text>
    <div class="XHRM-login-form">
      <div class="XHRM-login-error">
        <oxd-alert
          :show="error !== null"
          :message="error?.message || ''"
          type="error"
        ></oxd-alert>
        <oxd-sheet
          v-if="isDemoMode"
          type="gray-lighten-2"
          class="XHRM-demo-credentials"
        >
          <oxd-text tag="p">Username : Admin</oxd-text>
          <oxd-text tag="p">Password : admin123</oxd-text>
        </oxd-sheet>
      </div>
      <oxd-form
        ref="loginForm"
        method="post"
        :action="submitUrl"
        @submit-valid="onSubmit"
      >
        <input name="_token" :value="token" type="hidden" />

        <oxd-form-row>
          <oxd-input-field
            v-model="username"
            name="username"
            :label="$t('general.username')"
            label-icon="person"
            :placeholder="$t('auth.username')"
            :rules="rules.username"
            autofocus
          />
        </oxd-form-row>

        <oxd-form-row>
          <oxd-input-field
            v-model="password"
            name="password"
            :label="$t('general.password')"
            label-icon="key"
            :placeholder="$t('auth.password')"
            type="password"
            :rules="rules.password"
          />
        </oxd-form-row>

        <oxd-form-actions class="XHRM-login-action">
          <oxd-button
            class="XHRM-login-button"
            display-type="main"
            :label="$t('auth.login')"
            type="submit"
          />
        </oxd-form-actions>
        <div class="XHRM-login-forgot">
          <oxd-text class="XHRM-login-forgot-header" @click="navigateUrl">
            {{ $t('auth.forgot_password') }}?
          </oxd-text>
        </div>
      </oxd-form>
      <template v-if="authenticators.length > 0">
        <oxd-divider class="XHRM-login-seperator"></oxd-divider>
        <social-media-auth :authenticators="authenticators"></social-media-auth>
      </template>
    </div>
    <div class="XHRM-login-footer">
      <div v-if="showSocialMedia" class="XHRM-login-footer-sm">
        <a
          href="https://www.linkedin.com/company/XHRM/mycompany/"
          target="_blank"
        >
          <oxd-icon type="svg" class="XHRM-sm-icon" name="linkedinFill" />
        </a>
        <a href="https://www.facebook.com/XHRM/" target="_blank">
          <oxd-icon type="svg" class="XHRM-sm-icon" name="facebookFill" />
        </a>
        <a href="https://twitter.com/XHRM?lang=en" target="_blank">
          <oxd-icon type="svg" class="XHRM-sm-icon" name="twitterFill" />
        </a>
        <a href="https://www.youtube.com/c/XHRMInc" target="_blank">
          <oxd-icon type="svg" class="XHRM-sm-icon" name="youtubeFill" />
        </a>
      </div>
      <slot name="footer"></slot>
    </div>
  </login-layout>
</template>

<script>
import {urlFor} from '@ohrm/core/util/helper/url';
import {OxdAlert, OxdIcon, OxdSheet} from '@ohrm/oxd';
import {required} from '@ohrm/core/util/validation/rules';
import {navigate, reloadPage} from '@ohrm/core/util/helper/navigation';
import LoginLayout from '@/XHRMAuthenticationPlugin/components/LoginLayout.vue';
import SocialMediaAuth from '@/XHRMAuthenticationPlugin/components/SocialMediaAuth.vue';

export default {
  components: {
    'oxd-icon': OxdIcon,
    'oxd-alert': OxdAlert,
    'oxd-sheet': OxdSheet,
    'login-layout': LoginLayout,
    'social-media-auth': SocialMediaAuth,
  },

  props: {
    error: {
      type: Object,
      default: () => null,
    },
    token: {
      type: String,
      required: true,
    },
    showSocialMedia: {
      type: Boolean,
      default: true,
    },
    isDemoMode: {
      type: Boolean,
      default: false,
    },
    authenticators: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      username: '',
      password: '',
      rules: {
        username: [required],
        password: [required],
      },
      submitted: false,
    };
  },

  computed: {
    submitUrl() {
      return urlFor('/auth/validate');
    },
  },

  beforeMount() {
    setTimeout(() => {
      reloadPage();
    }, 1200000); // 20 * 60 * 1000 (20 minutes);
  },

  methods: {
    onSubmit() {
      if (!this.submitted) {
        this.submitted = true;
        this.$refs.loginForm.$el.submit();
      }
    },
    navigateUrl() {
      navigate('/auth/requestPasswordResetCode');
    },
  },
};
</script>

<style src="./login.scss" lang="scss" scoped></style>
