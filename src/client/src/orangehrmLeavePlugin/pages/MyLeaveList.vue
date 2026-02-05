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
  <leave-list-table :leave-statuses="leaveStatuses" :my-leave-list="true">
    <template #default="{filters, filterItems, rules, onReset}">
      <oxd-table-filter :filter-title="$t('leave.my_leave_list')">
        <oxd-form @submit-valid="filterItems" @reset="onReset">
          <oxd-form-row>
            <oxd-grid :cols="4" class="XHRM-full-width-grid">
              <oxd-grid-item>
                <date-input
                  v-model="filters.fromDate"
                  :label="$t('general.from_date')"
                  :rules="rules.fromDate"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <date-input
                  v-model="filters.toDate"
                  :label="$t('general.to_date')"
                  :rules="rules.toDate"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="filters.statuses"
                  value="Select"
                  type="multiselect"
                  :label="$t('leave.show_leave_with_status')"
                  :options="leaveStatuses"
                  :rules="rules.statuses"
                  required
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <leave-type-dropdown
                  v-model="filters.leaveType"
                  :eligible-only="true"
                  :include-allocated="true"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>

          <oxd-divider />

          <oxd-form-actions>
            <required-text />
            <oxd-button
              display-type="ghost"
              :label="$t('general.reset')"
              type="reset"
            />
            <oxd-button
              class="XHRM-left-space"
              display-type="secondary"
              :label="$t('general.search')"
              type="submit"
            />
          </oxd-form-actions>
        </oxd-form>
      </oxd-table-filter>
    </template>
  </leave-list-table>
</template>

<script>
import LeaveListTable from '@/XHRMLeavePlugin/components/LeaveListTable';
import LeaveTypeDropdown from '@/XHRMLeavePlugin/components/LeaveTypeDropdown';

export default {
  components: {
    'leave-list-table': LeaveListTable,
    'leave-type-dropdown': LeaveTypeDropdown,
  },
  props: {
    leaveStatuses: {
      type: Array,
      default: () => [],
    },
  },
};
</script>

