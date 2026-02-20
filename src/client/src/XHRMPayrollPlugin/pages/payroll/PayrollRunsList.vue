<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Payroll Runs</oxd-text>
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
    const statusLabels = {
      draft: 'Draft',
      pending_approval: 'Pending Approval',
      approved: 'Approved',
      rejected: 'Rejected',
      paid: 'Paid',
    };

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        period: `${item.periodStart} — ${item.periodEnd}`,
        periodType: item.periodType.charAt(0).toUpperCase() + item.periodType.slice(1),
        status: statusLabels[item.status] || item.status,
        employees: item.employeeCount,
        totalGross: `PKR ${Number(item.totalGross).toLocaleString()}`,
        totalNet: `PKR ${Number(item.totalNet).toLocaleString()}`,
        generatedAt: item.generatedAt,
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/runs',
    );
    const {
      showPaginator,
      currentPage,
      total,
      pages,
      isLoading,
      execQuery,
    } = usePaginate(http, {normalizer: dataNormalizer});

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      execQuery,
      items: usePaginate(http, {normalizer: dataNormalizer}).response,
    };
  },
  data() {
    return {
      headers: [
        {name: 'period', title: 'Pay Period', style: {flex: 2}},
        {name: 'periodType', title: 'Type', style: {flex: 1}},
        {name: 'employees', title: 'Employees', style: {flex: 1}},
        {name: 'totalGross', title: 'Total Gross', style: {flex: 2}},
        {name: 'totalNet', title: 'Total Net', style: {flex: 2}},
        {name: 'status', title: 'Status', style: {flex: 1}},
        {
          name: 'actions',
          title: 'Actions',
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
      navigate('/payroll/approvePayroll/{id}', {id: item.id});
    },
  },
};
</script>
