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
  <oxd-form
    :loading="isLoading"
    :class="
      isLoading
        ? 'XHRM-system-check-form-loading'
        : 'XHRM-system-check-form'
    "
  >
    <div class="XHRM-system-check">
      <oxd-text tag="h5" class="XHRM-system-check-title">
        System Check
      </oxd-text>
      <br />
      <oxd-text class="XHRM-system-check-content">
        To properly function the system, please ensure that all of the system
        check items listed below are green. If any are red, please take the
        necessary steps to fix them.
      </oxd-text>
      <br />
      <oxd-text
        v-if="error?.message"
        class="XHRM-system-check-content --error"
      >
        An unexpected error occurred. Please provide the file write permission
        to <b>/src/log</b> directory and check the error log in
        <b>/src/log/XHRM.log</b> file for more details.
      </oxd-text>
      <flex-table
        v-for="item in items"
        :key="item.category"
        :items="item.checks"
        :title-name="item.category"
      ></flex-table>
      <oxd-form-actions class="XHRM-system-check-action">
        <oxd-button
          class="XHRM-left-space"
          display-type="ghost"
          label="Re-Check"
          type="submit"
          @click="reCheck"
        />
      </oxd-form-actions>
    </div>
    <slot name="footer"></slot>
  </oxd-form>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import FlexTable from '@/XHRMSystemCheckPlugin/components/FlexTable';
export default {
  name: 'SystemCheckScreen',
  components: {
    'flex-table': FlexTable,
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/core/system-check`,
    );
    return {
      http,
    };
  },
  data() {
    return {
      items: [],
      isLoading: false,
      isInterrupted: false,
      error: null,
    };
  },
  beforeMount() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.isLoading = true;
      this.http
        .getAll()
        .then((response) => {
          const {data, meta} = response.data;
          this.items = data;
          this.isInterrupted = meta.isInterrupted;
          this.error = meta.error;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    reCheck() {
      this.fetchData();
    },
  },
};
</script>

<style scoped lang="scss">
.XHRM-system-check {
  font-size: $oxd-input-control-font-size;
  &-title {
    font-weight: 700;
    color: $oxd-primary-one-color;
  }
  &-content {
    &.--error {
      color: $oxd-feedback-danger-color;
    }
  }
  &-action {
    padding: 1rem 0;
  }
  &-form {
    margin: 5%;
    &-loading {
      height: 100%;
    }
  }
}
</style>

