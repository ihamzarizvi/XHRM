<template>
  <oxd-table-filter :filter-title="'Salary Components'">
    <oxd-form @submit-valid="filterItems">
      <oxd-form-row>
        <oxd-grid :cols="3" class="XHRM-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="filters.type"
              type="select"
              label="Type"
              :options="typeOptions"
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
        <oxd-button
          display-type="ghost"
          label="Reset"
          @click="onClickReset"
        />
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
        label="Add"
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
        v-model:order="sortDefinition"
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
  <save-salary-component
    v-if="showSaveModal"
    :data="editData"
    @close="onCloseSaveModal"
    @save="onSaveComponent"
  />
</template>

<script>
import {ref, computed} from 'vue';
import useSort from '@ohrm/core/util/composable/useSort';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog.vue';
import SaveSalaryComponent from '@/XHRMPayrollPlugin/components/SaveSalaryComponent.vue';

const defaultFilters = {
  type: null,
  status: null,
};

const defaultSortOrder = {
  'salaryComponent.sortOrder': 'ASC',
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'save-salary-component': SaveSalaryComponent,
  },
  setup() {
    const filters = ref({...defaultFilters});
    const {sortDefinition, sortField, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });

    const serializedFilters = computed(() => {
      return {
        type: filters.value.type?.id ?? null,
        isActive: filters.value.status?.id ?? null,
        sortField: sortField.value,
        sortOrder: sortOrder.value,
      };
    });

    const dataNormalizer = (data) => {
      return data.map((item) => {
        const typeLabel = item.type === 'earning'
          ? 'Earning'
          : 'Deduction';
        const calcLabel = {
          fixed: 'Fixed',
          percentage: 'Percentage',
          formula: 'Formula',
          auto: 'Auto-calculated',
        }[item.calculationType] || item.calculationType;

        return {
          id: item.id,
          name: item.name,
          code: item.code,
          type: typeLabel,
          calculationType: calcLabel,
          defaultValue: item.defaultValue ? `${item.defaultValue}` : '-',
          status: item.isActive ? 'Active' : 'Inactive',
        };
      });
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/salary-components',
    );
    const {
      showPaginator,
      currentPage,
      total,
      pages,
      isLoading,
      execQuery,
    } = usePaginate(http, {
      normalizer: dataNormalizer,
      query: serializedFilters,
    });
    onSort(execQuery);

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      execQuery,
      items: usePaginate(http, {normalizer: dataNormalizer, query: serializedFilters}).response,
      filters,
      sortDefinition,
    };
  },
  data() {
    return {
      headers: [
        {name: 'name', title: 'Name', sortField: 'salaryComponent.name', style: {flex: 2}},
        {name: 'code', title: 'Code', style: {flex: 1}},
        {name: 'type', title: 'Type', style: {flex: 1}},
        {name: 'calculationType', title: 'Calculation', style: {flex: 1}},
        {name: 'defaultValue', title: 'Default Value', style: {flex: 1}},
        {name: 'status', title: 'Status', style: {flex: 1}},
        {
          name: 'actions',
          title: 'Actions',
          slot: 'action',
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {name: 'trash'},
            },
            edit: {
              onClick: this.onClickEdit,
              props: {name: 'pencil-fill'},
            },
          },
        },
      ],
      checkedItems: [],
      showSaveModal: false,
      editData: null,
      typeOptions: [
        {id: 'earning', label: 'Earning'},
        {id: 'deduction', label: 'Deduction'},
      ],
      statusOptions: [
        {id: true, label: 'Active'},
        {id: false, label: 'Inactive'},
      ],
    };
  },
  methods: {
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    async filterItems() {
      await this.execQuery();
    },
    onClickReset() {
      this.filters = {...defaultFilters};
      this.filterItems();
    },
    onClickAdd() {
      this.editData = null;
      this.showSaveModal = true;
    },
    onClickEdit(item) {
      this.editData = item;
      this.showSaveModal = true;
    },
    onCloseSaveModal() {
      this.showSaveModal = false;
      this.editData = null;
    },
    async onSaveComponent() {
      this.showSaveModal = false;
      this.editData = null;
      await this.resetDataTable();
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
        this.resetDataTable();
      });
    },
  },
};
</script>
