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
  <oxd-dialog class="XHRM-dialog-popup" @update:show="onClose">
    <div class="XHRM-modal-header">
      <oxd-text type="card-title">{{ $t('pim.import_details') }}</oxd-text>
    </div>
    <div class="XHRM-text-center-align">
      <oxd-text
        type="card-body"
        :class="{
          'XHRM-success-message': data.success > 0,
        }"
      >
        {{ $t('pim.n_records_successfully_imported', {count: data.success}) }}
      </oxd-text>
      <template v-if="data.failed > 0">
        <oxd-text type="card-body" class="XHRM-error-message">
          {{ $t('pim.n_records_failed_to_import', {count: data.failed}) }}
        </oxd-text>
        <oxd-text type="card-body" class="XHRM-error-message">
          {{ $t('pim.failed_rows') }}
        </oxd-text>
        <oxd-text type="card-body" class="XHRM-error-message">
          {{ data.failedRows.toString() }}
        </oxd-text>
      </template>
    </div>
    <div class="XHRM-modal-footer">
      <oxd-button
        display-type="secondary"
        :label="$t('general.ok')"
        @click="onClose"
      />
    </div>
  </oxd-dialog>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';

export default {
  name: 'EmployeeDataImportModal',
  components: {
    'oxd-dialog': OxdDialog,
  },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  emits: ['close'],
  methods: {
    onClose() {
      this.$emit('close', true);
    },
  },
};
</script>

<style lang="scss" scoped>
.XHRM-modal-header {
  display: flex;
  margin-bottom: 1.2rem;
  justify-content: center;
}
.XHRM-modal-footer {
  display: flex;
  margin-top: 1.2rem;
  justify-content: center;
}
.XHRM-text-center-align {
  text-align: center;
  overflow-wrap: break-word;
}
::v-deep(.XHRM-dialog-popup) {
  width: 450px;
}
.XHRM-success-message {
  color: $oxd-feedback-success-color;
}
.XHRM-error-message {
  color: $oxd-feedback-danger-color;
}
</style>

