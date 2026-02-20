<template>
  <oxd-table-filter :filter-title="'Loans & Advances'">
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
              v-model="filters.status"
              type="select"
              label="Status"
              :options="statusOptions"
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
    <div class="XHRM-header-container">
      <oxd-button
        label="Add Loan"
        icon-name="plus"
        display-type="secondary"
        @click="onClickAdd"
      />
    </div>
    <table-header
      :total="total"
      :loading="isLoading"
      :selected="checkedItems.length"
      @delete="onClickDeleteSelected"
    />
    <div class="XHRM-container">
      <oxd-card-table
        v-model:selected="checkedItems"
        :items="items.data"
        :headers="headers"
        :selectable="true"
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
  <delete-confirmation ref="deleteDialog"></delete-confirmation>
</template>

<script>
import {ref, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog.vue';

const defaultFilters = {
  employeeName: '',
  status: null,
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
  },
  setup() {
    const filters = ref({...defaultFilters});

    const serializedFilters = computed(() => ({
      employeeName: filters.value.employeeName || null,
      status: filters.value.status?.id ?? null,
    }));

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        employeeName: `${item.employee?.firstName || ''} ${
          item.employee?.lastName || ''
        }`,
        type: item.loanType === 'loan' ? 'Loan' : 'Advance',
        totalAmount: `PKR ${Number(item.totalAmount).toLocaleString()}`,
        monthlyDeduction: `PKR ${Number(
          item.monthlyDeduction,
        ).toLocaleString()}`,
        remaining: `PKR ${Number(item.remainingAmount).toLocaleString()}`,
        startDate: item.startDate,
        status: item.status.charAt(0).toUpperCase() + item.status.slice(1),
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/loans',
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
        {name: 'type', title: 'Type', style: {flex: 1}},
        {name: 'totalAmount', title: 'Total Amount', style: {flex: 2}},
        {
          name: 'monthlyDeduction',
          title: 'Monthly Deduction',
          style: {flex: 2},
        },
        {name: 'remaining', title: 'Remaining', style: {flex: 2}},
        {name: 'startDate', title: 'Start Date', style: {flex: 1}},
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
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {name: 'trash'},
            },
          },
        },
      ],
      checkedItems: [],
      statusOptions: [
        {id: 'active', label: 'Active'},
        {id: 'completed', label: 'Completed'},
        {id: 'cancelled', label: 'Cancelled'},
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
    onClickAdd() {
      // TODO: Show add loan modal
    },
    onClickEdit(item) {
      // TODO: Show edit loan modal
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => this.items?.data[index].id);
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') this.deleteItems(ids);
      });
    },
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') this.deleteItems([item.id]);
      });
    },
    deleteItems(items) {
      this.isLoading = true;
      this.http.deleteAll({ids: items}).then(() => {
        this.$toast.deleteSuccess();
        this.isLoading = false;
        this.checkedItems = [];
        this.execQuery();
      });
    },
  },
};
</script>
