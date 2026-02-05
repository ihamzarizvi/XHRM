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
  <edit-employee-layout :employee-id="empNumber" screen="report-to">
    <div class="XHRM-horizontal-padding XHRM-top-padding">
      <oxd-text tag="h6" class="XHRM-main-title">{{
        $t('pim.report_to')
      }}</oxd-text>
      <oxd-divider />
    </div>
    <employee-supervisors
      :emp-number="empNumber"
      :reporting-methods="reportingMethods"
    ></employee-supervisors>
    <employee-subordinates
      :emp-number="empNumber"
      :reporting-methods="reportingMethods"
    ></employee-subordinates>
  </edit-employee-layout>
</template>

<script>
import EditEmployeeLayout from '@/XHRMPimPlugin/components/EditEmployeeLayout';
import EmployeeSupervisors from '@/XHRMPimPlugin/components/EmployeeSupervisors';
import EmployeeSubordinates from '@/XHRMPimPlugin/components/EmployeeSubordinates';

export default {
  components: {
    'edit-employee-layout': EditEmployeeLayout,
    'employee-supervisors': EmployeeSupervisors,
    'employee-subordinates': EmployeeSubordinates,
  },
  props: {
    empNumber: {
      type: String,
      required: true,
    },
    reportingMethods: {
      type: Array,
      required: true,
    },
  },
};
</script>
