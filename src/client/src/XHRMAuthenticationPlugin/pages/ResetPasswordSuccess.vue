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
  <div class="XHRM-forgot-password-container">
    <div class="XHRM-forgot-password-wrapper">
      <div class="XHRM-card-container">
        <oxd-text tag="h6" class="XHRM-forgot-password-title">
          {{ $t('auth.reset_password_link_sent_successfully') }}
        </oxd-text>
        <oxd-divider />
        <card-note
          :note-text="$t('auth.reset_password_link_via_email')"
          class="XHRM-forgot-password-card-note"
        />
        <card-note
          :note-text="$t('auth.follow_link_to_select_password')"
          class="XHRM-forgot-password-card-note"
        />
        <card-note
          :note-text="$t('auth.email_not_receive_note')"
          class="XHRM-forgot-password-card-note"
          has-note
        />
      </div>
    </div>
    <slot name="footer"></slot>
  </div>
</template>

<script>
import CardNote from '../components/CardNote';

export default {
  name: 'ResetPasswordSuccess',
  components: {
    'card-note': CardNote,
  },
};
</script>

<style src="./reset-password.scss" lang="scss" scoped></style>
