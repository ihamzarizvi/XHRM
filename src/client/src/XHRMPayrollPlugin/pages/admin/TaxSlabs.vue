<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Tax Slabs</oxd-text>
      </div>
      <oxd-form-row>
        <oxd-grid :cols="3" class="XHRM-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="selectedYear"
              type="select"
              label="Financial Year"
              :options="financialYears"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-divider />
      <div class="XHRM-header-container">
        <oxd-button
          label="Add Slab"
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
import {ref, computed, watch} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';

export default {
  setup() {
    const selectedYear = ref(null);
    const financialYears = ref([]);

    const yearHttp = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/financial-years',
    );
    yearHttp.getAll().then((response) => {
      const data = response.data?.data || [];
      financialYears.value = data.map((fy) => ({
        id: fy.id,
        label: `${fy.label} (${fy.status})`,
      }));
      if (financialYears.value.length > 0) {
        selectedYear.value = financialYears.value[0];
      }
    });

    const serializedFilters = computed(() => ({
      financialYearId: selectedYear.value?.id ?? null,
    }));

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        minIncome: `PKR ${Number(item.minIncome).toLocaleString()}`,
        maxIncome: item.maxIncome
          ? `PKR ${Number(item.maxIncome).toLocaleString()}`
          : 'No Limit',
        taxRate: `${item.taxRate}%`,
        fixedAmount: `PKR ${Number(item.fixedAmount).toLocaleString()}`,
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/tax-slabs',
    );
    const {total, isLoading, execQuery} = usePaginate(http, {
      normalizer: dataNormalizer,
      query: serializedFilters,
    });

    watch(selectedYear, () => execQuery());

    return {
      http,
      selectedYear,
      financialYears,
      isLoading,
      total,
      execQuery,
      items: usePaginate(http, {normalizer: dataNormalizer, query: serializedFilters}).response,
    };
  },
  data() {
    return {
      headers: [
        {name: 'minIncome', title: 'Min Annual Income', style: {flex: 2}},
        {name: 'maxIncome', title: 'Max Annual Income', style: {flex: 2}},
        {name: 'taxRate', title: 'Tax Rate', style: {flex: 1}},
        {name: 'fixedAmount', title: 'Fixed Amount', style: {flex: 2}},
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
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {name: 'trash'},
            },
          },
        },
      ],
    };
  },
  methods: {
    onClickAdd() {
      // TODO: Show add slab modal
    },
    onClickEdit(item) {
      // TODO: Show edit slab modal
    },
    onClickDelete(item) {
      // TODO: Delete slab
    },
  },
};
</script>
