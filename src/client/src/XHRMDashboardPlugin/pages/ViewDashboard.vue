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
  <oxd-grid class="XHRM-dashboard-grid" :cols="3">
    <oxd-grid-item
      v-if="$can.read('dashboard_time_widget')"
      class="XHRM-dashboard-widget"
    >
      <employee-attendance-widget></employee-attendance-widget>
    </oxd-grid-item>
    <oxd-grid-item class="XHRM-dashboard-widget">
      <my-action-summary-widget></my-action-summary-widget>
    </oxd-grid-item>
    <oxd-grid-item class="XHRM-dashboard-widget">
      <quick-launch-widget></quick-launch-widget>
    </oxd-grid-item>
    <oxd-grid-item
      v-if="$can.read('dashboard_buzz_widget')"
      class="XHRM-dashboard-widget"
    >
      <buzz-latest-post-widget></buzz-latest-post-widget>
    </oxd-grid-item>
    <oxd-grid-item
      v-if="$can.read('dashboard_leave_widget')"
      class="XHRM-dashboard-widget"
    >
      <employees-on-leave-widget></employees-on-leave-widget>
    </oxd-grid-item>
    <oxd-grid-item
      v-if="$can.read('dashboard_subunit_widget')"
      class="XHRM-dashboard-widget"
    >
      <employee-subunit-widget></employee-subunit-widget>
    </oxd-grid-item>
    <oxd-grid-item
      v-if="$can.read('dashboard_location_widget')"
      class="XHRM-dashboard-widget"
    >
      <employee-location-widget></employee-location-widget>
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import QuickLaunchWidget from '@/XHRMDashboardPlugin/components/QuickLaunchWidget.vue';
import BuzzLatestPostWidget from '@/XHRMDashboardPlugin/components/BuzzLatestPostWidget.vue';
import EmployeeSubunitWidget from '@/XHRMDashboardPlugin/components/EmployeeSubunitWidget.vue';
import MyActionSummaryWidget from '@/XHRMDashboardPlugin/components/MyActionSummaryWidget.vue';
import EmployeeLocationWidget from '@/XHRMDashboardPlugin/components/EmployeeLocationWidget.vue';
import EmployeesOnLeaveWidget from '@/XHRMDashboardPlugin/components/EmployeesOnLeaveWidget.vue';
import EmployeeAttendanceWidget from '@/XHRMDashboardPlugin/components/EmployeeAttendanceWidget.vue';

export default {
  components: {
    'quick-launch-widget': QuickLaunchWidget,
    'buzz-latest-post-widget': BuzzLatestPostWidget,
    'employee-subunit-widget': EmployeeSubunitWidget,
    'my-action-summary-widget': MyActionSummaryWidget,
    'employee-location-widget': EmployeeLocationWidget,
    'employees-on-leave-widget': EmployeesOnLeaveWidget,
    'employee-attendance-widget': EmployeeAttendanceWidget,
  },
  mounted() {
    const http = new APIService(window.appGlobal.baseUrl, '/events/push');
    http.create();
  },
};
</script>

<style lang="scss" scoped>
.XHRM-dashboard-grid {
  margin: 0 auto;
  box-sizing: border-box;
  max-width: calc(350px * 3);
  grid-template-columns: repeat(auto-fill, minmax(max(320px, 100%/3), 1fr));
}
</style>
