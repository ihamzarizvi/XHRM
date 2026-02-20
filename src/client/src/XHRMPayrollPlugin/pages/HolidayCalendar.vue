<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Holiday Calendar</oxd-text>
        <oxd-button
          label="Add Holiday"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <oxd-form-row>
        <oxd-grid :cols="3" class="XHRM-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="selectedYear"
              type="select"
              label="Year"
              :options="yearOptions"
              @update:modelValue="onYearChange"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-divider />
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
  </div>
  <delete-confirmation ref="deleteDialog"></delete-confirmation>
</template>

<script>
import {ref, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog.vue';

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
  },
  setup() {
    const currentYearValue = new Date().getFullYear();
    const yearOptions = [];
    for (let y = currentYearValue - 2; y <= currentYearValue + 2; y++) {
      yearOptions.push({id: y, label: String(y)});
    }
    const selectedYear = ref(yearOptions.find((y) => y.id === currentYearValue));

    const serializedFilters = computed(() => ({
      year: selectedYear.value?.id ?? currentYearValue,
    }));

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        date: item.date,
        name: item.name,
        type: item.isRecurring ? 'Recurring' : 'One-time',
        halfDay: item.isHalfDay ? 'Yes' : 'No',
        appliesTo: item.appliesTo === 'all' ? 'All Employees' : 'Specific Department',
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/holidays',
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

    return {
      http,
      selectedYear,
      yearOptions,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      execQuery,
      items: usePaginate(http, {normalizer: dataNormalizer, query: serializedFilters}).response,
    };
  },
  data() {
    return {
      headers: [
        {name: 'date', title: 'Date', style: {flex: 2}},
        {name: 'name', title: 'Holiday Name', style: {flex: 3}},
        {name: 'type', title: 'Type', style: {flex: 1}},
        {name: 'halfDay', title: 'Half Day', style: {flex: 1}},
        {name: 'appliesTo', title: 'Applies To', style: {flex: 2}},
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
    };
  },
  methods: {
    onYearChange() {
      this.execQuery();
    },
    onClickAdd() {
      // TODO: Show add holiday modal
    },
    onClickEdit(item) {
      // TODO: Show edit holiday modal
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
