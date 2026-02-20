<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Financial Year</oxd-text>
        <oxd-button
          label="Add"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
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
    </div>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';

export default {
  setup() {
    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        label: item.label,
        startDate: item.startDate,
        endDate: item.endDate,
        status: item.status === 'active' ? 'Active' : 'Closed',
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/financial-years',
    );
    const {total, isLoading, execQuery} = usePaginate(http, {
      normalizer: dataNormalizer,
    });

    return {
      http,
      isLoading,
      total,
      execQuery,
      items: usePaginate(http, {normalizer: dataNormalizer}).response,
    };
  },
  data() {
    return {
      headers: [
        {name: 'label', title: 'Financial Year', style: {flex: 2}},
        {name: 'startDate', title: 'Start Date', style: {flex: 2}},
        {name: 'endDate', title: 'End Date', style: {flex: 2}},
        {name: 'status', title: 'Status', style: {flex: 1}},
        {
          name: 'actions',
          title: 'Actions',
          slot: 'action',
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            edit: {
              onClick: this.onClickEdit,
              props: {name: 'pencil-fill'},
            },
          },
        },
      ],
    };
  },
  methods: {
    onClickAdd() {
      // TODO: Show add modal
    },
    onClickEdit(item) {
      // TODO: Show edit modal  
    },
  },
};
</script>
