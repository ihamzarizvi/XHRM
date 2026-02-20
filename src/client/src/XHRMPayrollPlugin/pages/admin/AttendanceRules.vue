<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Attendance Rules</oxd-text>
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
  <save-attendance-rule
    v-if="showSaveModal"
    :data="editData"
    @close="onCloseSaveModal"
    @save="onSaveRule"
  />
</template>

<script>
import {ref} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import SaveAttendanceRule from '@/XHRMPayrollPlugin/components/SaveAttendanceRule.vue';

export default {
  components: {
    'save-attendance-rule': SaveAttendanceRule,
  },
  setup() {
    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        name: item.name,
        gracePeriod: `${item.gracePeriodMinutes} min`,
        halfDayHours: `${item.halfDayHours} hrs`,
        latesPerAbsent: `${item.latesPerAbsent} lates = 1 absent`,
        isDefault: item.isDefault ? 'Yes' : 'No',
      }));
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/attendance-rules',
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
        {name: 'gracePeriod', title: 'Grace Period', style: {flex: 1}},
        {name: 'halfDayHours', title: 'Half Day Threshold', style: {flex: 1}},
        {name: 'latesPerAbsent', title: 'Lates → Absent', style: {flex: 2}},
        {name: 'isDefault', title: 'Default', style: {flex: 1}},
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
      showSaveModal: false,
      editData: null,
    };
  },
  methods: {
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
    async onSaveRule() {
      this.showSaveModal = false;
      this.editData = null;
      await this.execQuery();
    },
  },
};
</script>
