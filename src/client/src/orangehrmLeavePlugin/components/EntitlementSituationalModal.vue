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
  <oxd-dialog class="XHRM-dialog-popup" @update:show="onCancel">
    <div class="XHRM-modal-header">
      <oxd-text type="card-title">
        {{ $t('leave.situational_leave') }}
      </oxd-text>
    </div>
    <div class="XHRM-text-center-align">
      <oxd-text type="card-body">
        {{ $t('leave.entitlement_situational_description') }}
      </oxd-text>
    </div>
    <div class="XHRM-modal-footer">
      <oxd-form-actions>
        <oxd-button
          type="submit"
          display-type="secondary"
          :label="$t('general.ok')"
          @click="onCancel"
        />
      </oxd-form-actions>
    </div>
  </oxd-dialog>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';

export default {
  name: 'EntitlementSituationalModal',
  components: {
    'oxd-dialog': OxdDialog,
  },
  emits: ['close'],
  methods: {
    onCancel() {
      this.$emit('close', true);
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

.XHRM-text-center-align {
  text-align: center;
}
</style>

