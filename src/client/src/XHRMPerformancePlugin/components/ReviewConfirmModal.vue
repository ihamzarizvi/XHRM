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
  <teleport to="#app">
    <oxd-dialog v-if="show" class="XHRM-dialog-popup" @update:show="onCancel">
      <div class="XHRM-modal-header">
        <oxd-text type="card-title">
          {{ $t('performance.confirm_review_submission') }}
        </oxd-text>
      </div>
      <div class="XHRM-text-center-align">
        <oxd-text type="subtitle-2">
          {{ $t('performance.confirm_review_submission_message') }}
        </oxd-text>
      </div>
      <div class="XHRM-modal-footer">
        <oxd-button
          display-type="ghost"
          class="XHRM-button-margin"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <oxd-button
          display-type="secondary"
          class="XHRM-button-margin"
          :label="$t('general.ok')"
          @click="onConfirm"
        />
      </div>
    </oxd-dialog>
  </teleport>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';

export default {
  name: 'ReviewConfirmModal',
  components: {
    'oxd-dialog': OxdDialog,
  },
  data() {
    return {
      show: false,
      reject: null,
      resolve: null,
    };
  },
  methods: {
    showDialog() {
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
        this.show = true;
      });
    },
    onConfirm() {
      this.show = false;
      this.resolve && this.resolve('ok');
    },
    onCancel() {
      this.show = false;
      this.resolve && this.resolve('cancel');
    },
  },
};
</script>

<style scoped>
.XHRM-modal-header {
  margin-bottom: 1.2rem;
  display: flex;
  justify-content: center;
}
.XHRM-modal-footer {
  margin-top: 1.2rem;
  display: flex;
  justify-content: center;
}
.XHRM-button-margin {
  margin: 0.25rem;
}
.XHRM-text-center-align {
  text-align: center;
}
</style>
