<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">My Payslips</oxd-text>
      </div>
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
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import {navigate} from '@/core/util/helper/navigation';

export default {
  setup() {
    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        period: `${item.payrollRun?.periodStart || ''} — ${
          item.payrollRun?.periodEnd || ''
        }`,
        grossSalary: `PKR ${Number(item.grossSalary).toLocaleString()}`,
        deductions: `PKR ${Number(item.totalDeductions).toLocaleString()}`,
        netSalary: `PKR ${Number(item.netSalary).toLocaleString()}`,
        status: item.status.charAt(0).toUpperCase() + item.status.slice(1),
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/my-payslips',
    );
    const {showPaginator, currentPage, total, pages, isLoading} = usePaginate(
      http,
      {normalizer: dataNormalizer},
    );

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      items: usePaginate(http, {normalizer: dataNormalizer}).response,
    };
  },
  data() {
    return {
      headers: [
        {name: 'period', title: 'Pay Period', style: {flex: 2}},
        {name: 'grossSalary', title: 'Gross Salary', style: {flex: 2}},
        {name: 'deductions', title: 'Deductions', style: {flex: 2}},
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
    onClickView(item) {
      navigate('/payroll/payslip/{id}', {id: item.id});
    },
  },
};
</script>
