<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Overtime Rules</oxd-text>
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
        name: item.name,
        type: item.type.charAt(0).toUpperCase() + item.type.slice(1),
        rate: `${item.rateMultiplier}×`,
        maxHours: `${item.maxOtHoursPerDay} hrs/day`,
        status: item.isActive ? 'Active' : 'Inactive',
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/overtime-rules',
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
        {name: 'name', title: 'Rule Name', style: {flex: 2}},
        {name: 'type', title: 'Type', style: {flex: 1}},
        {name: 'rate', title: 'Rate Multiplier', style: {flex: 1}},
        {name: 'maxHours', title: 'Max OT/Day', style: {flex: 1}},
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
