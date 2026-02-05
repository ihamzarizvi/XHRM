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
  <div class="XHRM-project-admin-input">
    <employee-autocomplete
      :label="!showDelete ? $t('time.project_admin') : null"
      api-path="/api/v2/time/project-admins"
      :params="{
        includeEmployees,
      }"
      v-bind="$attrs"
    />
    <oxd-icon-button
      v-if="showDelete"
      name="trash-fill"
      class="XHRM-project-delete-icon"
      :with-container="false"
      @click="remove"
    />
  </div>
</template>

<script>
import EmployeeAutocomplete from '@/core/components/inputs/EmployeeAutocomplete.vue';

export default {
  name: 'ProjectAdminAutocomplete',
  components: {
    'employee-autocomplete': EmployeeAutocomplete,
  },
  inheritAttrs: false,
  props: {
    showDelete: {
      type: Boolean,
      required: true,
    },
    includeEmployees: {
      type: String,
      default: 'currentAndPast',
    },
  },
  emits: ['remove'],
  methods: {
    remove() {
      this.$emit('remove');
    },
  },
};
</script>

<style lang="scss" scoped>
.XHRM-project {
  &-admin-input {
    display: flex;
    align-items: flex-start;
    ::v-deep(.oxd-input-group__label-wrapper:empty) {
      display: none;
    }
  }
  &-delete-icon {
    margin-left: 1rem;
    margin-top: 1rem;
  }
}
</style>

