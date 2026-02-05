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
    <simple-dialog
      v-if="show"
      :with-close="false"
      class="XHRM-confirmation-dialog XHRM-dialog-popup"
      @update:show="onSuccess"
    >
      <div class="XHRM-modal-header">
        <oxd-text type="card-title">
          {{ $t('recruitment.application_received') }}
        </oxd-text>
      </div>
      <div class="XHRM-text-center-align">
        <oxd-text type="card-body">
          {{
            $t('recruitment.your_application_has_been_submitted_successfully')
          }}
        </oxd-text>
      </div>
      <div class="XHRM-modal-footer">
        <oxd-button
          :label="$t('general.ok')"
          display-type="text"
          class="XHRM-button-margin"
          @click="onSuccess"
        />
      </div>
    </simple-dialog>
  </teleport>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';

export default {
  name: 'SuccessDialog',
  components: {
    'simple-dialog': OxdDialog,
  },
  data() {
    return {
      show: false,
      resolve: null,
    };
  },
  methods: {
    showSuccessDialog() {
      return new Promise((resolve) => {
        this.resolve = resolve;
        this.show = true;
      });
    },
    onSuccess() {
      this.show = false;
      this.resolve && this.resolve('ok');
    },
  },
};
</script>

<style lang="scss" scoped>
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

