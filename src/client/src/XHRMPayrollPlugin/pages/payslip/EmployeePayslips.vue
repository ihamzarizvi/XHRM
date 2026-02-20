<template>
  <oxd-table-filter :filter-title="'Employee Payslips'">
    <oxd-form @submit-valid="filterItems">
      <oxd-form-row>
        <oxd-grid :cols="3" class="XHRM-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="filters.employeeName"
              type="input"
              label="Employee Name"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="filters.fromDate"
              type="date"
              label="From Date"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="filters.toDate"
              type="date"
              label="To Date"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-divider />
      <oxd-form-actions>
        <oxd-button display-type="ghost" label="Reset" @click="onClickReset" />
        <oxd-button
          class="XHRM-left-space"
          display-type="secondary"
          label="Search"
          type="submit"
        />
      </oxd-form-actions>
    </oxd-form>
  </oxd-table-filter>
  <br />
  <div class="XHRM-paper-container">
    <table-header :total="total" :loading="isLoading" />
    <div class="XHRM-container">
      <oxd-card-table
        :items="items.data"
        :headers="headers"
        :selectable="false"
        :clickable="false"
        :loading="isLoading"
        row-decorator="oxd-table-decorator-card"
      />
    </div>
    <div class="XHRM-bottom-container">
      <oxd-pagination
        v-if="showPaginator"
        v-model:current="currentPage"
        :length="pages"
      />
    </div>
  </div>
</template>

<script>
import {ref, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import {navigate} from '@/core/util/helper/navigation';

const defaultFilters = {
  employeeName: '',
  fromDate: '',
  toDate: '',
};

export default {
  setup() {
    const filters = ref({...defaultFilters});

    const serializedFilters = computed(() => ({
      employeeName: filters.value.employeeName || null,
      fromDate: filters.value.fromDate || null,
      toDate: filters.value.toDate || null,
    }));

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        employeeName: `${item.employee?.firstName || ''} ${
          item.employee?.lastName || ''
        }`,
        payPeriod: item.payPeriodType,
        basicSalary: `PKR ${Number(item.basicSalary).toLocaleString()}`,
        netSalary: `PKR ${Number(item.netSalary).toLocaleString()}`,
        status: item.status,
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/payslips',
    );
    const {showPaginator, currentPage, total, pages, isLoading, execQuery} =
      usePaginate(http, {
        normalizer: dataNormalizer,
        query: serializedFilters,
      });

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      execQuery,
      items: usePaginate(http, {
        normalizer: dataNormalizer,
        query: serializedFilters,
      }).response,
      filters,
    };
  },
  data() {
    return {
      headers: [
        {name: 'employeeName', title: 'Employee', style: {flex: 2}},
        {name: 'payPeriod', title: 'Pay Period', style: {flex: 1}},
        {name: 'basicSalary', title: 'Basic Salary', style: {flex: 2}},
        {name: 'netSalary', title: 'Net Salary', style: {flex: 2}},
        {name: 'status', title: 'Status', style: {flex: 1}},
        {
          name: 'actions',
          title: '',
          slot: 'action',
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            view: {
              onClick: this.onClickView,
              props: {name: 'eye-fill'},
            },
          },
        },
      ],
    };
  },
  methods: {
    async filterItems() {
      await this.execQuery();
    },
    onClickReset() {
      this.filters = {...defaultFilters};
      this.filterItems();
    },
    onClickView(item) {
      navigate('/payroll/payslip/{id}', {id: item.id});
    },
  },
};
</script>
