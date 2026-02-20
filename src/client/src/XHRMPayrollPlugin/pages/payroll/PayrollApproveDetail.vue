<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">
          Payroll Run #{{ payrollRunId }} — Review & Approve
        </oxd-text>
      </div>

      <div v-if="runData" class="payroll-run-summary">
        <oxd-grid :cols="4" class="XHRM-full-width-grid payroll-stats">
          <oxd-grid-item>
            <div class="stat-card">
              <oxd-text tag="p" class="stat-label">Period</oxd-text>
              <oxd-text tag="h6" class="stat-value">{{ runData.periodStart }} — {{ runData.periodEnd }}</oxd-text>
            </div>
          </oxd-grid-item>
          <oxd-grid-item>
            <div class="stat-card">
              <oxd-text tag="p" class="stat-label">Employees</oxd-text>
              <oxd-text tag="h5" class="stat-value">{{ runData.employeeCount }}</oxd-text>
            </div>
          </oxd-grid-item>
          <oxd-grid-item>
            <div class="stat-card">
              <oxd-text tag="p" class="stat-label">Total Gross</oxd-text>
              <oxd-text tag="h5" class="stat-value">PKR {{ formatAmount(runData.totalGross) }}</oxd-text>
            </div>
          </oxd-grid-item>
          <oxd-grid-item>
            <div class="stat-card stat-card--net">
              <oxd-text tag="p" class="stat-label">Total Net Pay</oxd-text>
              <oxd-text tag="h5" class="stat-value">PKR {{ formatAmount(runData.totalNet) }}</oxd-text>
            </div>
          </oxd-grid-item>
        </oxd-grid>
      </div>

      <oxd-divider />
      <oxd-text tag="h6" class="XHRM-main-title">Employee Payslips</oxd-text>
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

      <oxd-divider />
      <oxd-form-row v-if="runData && runData.status === 'pending_approval'">
        <oxd-input-field
          v-model="rejectionNote"
          type="textarea"
          label="Note (optional, required if rejecting)"
        />
      </oxd-form-row>
      <oxd-form-actions v-if="runData && runData.status === 'pending_approval'">
        <oxd-button
          display-type="danger"
          label="Reject"
          @click="onReject"
        />
        <oxd-button
          class="XHRM-left-space"
          display-type="secondary"
          label="Approve Payroll"
          @click="onApprove"
        />
      </oxd-form-actions>
      <oxd-form-actions v-if="runData && runData.status === 'approved'">
        <oxd-button
          display-type="secondary"
          :label="emailSending ? 'Sending...' : 'Email All Payslips'"
          icon-name="envelope"
          :disabled="emailSending"
          @click="onEmailPayslips"
        />
      </oxd-form-actions>
      <div v-if="emailResult" class="email-result">
        <oxd-text tag="p">
          ✅ Sent: {{ emailResult.sent }} &nbsp; ❌ Failed: {{ emailResult.failed }} &nbsp; ⏭ Skipped: {{ emailResult.skipped }}
        </oxd-text>
      </div>
    </div>
  </div>
</template>

<script>
import {ref} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import {navigate} from '@/core/util/helper/navigation';

export default {
  props: {
    payrollRunId: {
      type: Number,
      required: true,
    },
  },
  setup(props) {
    const runData = ref(null);

    const runHttp = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/payroll/runs`,
    );
    runHttp.get(props.payrollRunId).then((response) => {
      runData.value = response.data?.data;
    });

    const dataNormalizer = (data) => {
      return data.map((item) => ({
        id: item.id,
        employeeName: `${item.employee?.firstName || ''} ${item.employee?.lastName || ''}`,
        basicSalary: `PKR ${Number(item.basicSalary).toLocaleString()}`,
        grossSalary: `PKR ${Number(item.grossSalary).toLocaleString()}`,
        deductions: `PKR ${Number(item.totalDeductions).toLocaleString()}`,
        netSalary: `PKR ${Number(item.netSalary).toLocaleString()}`,
        daysPresent: item.daysPresent,
        daysAbsent: item.daysAbsent,
      }));
    };

    const payslipHttp = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/payroll/runs/${props.payrollRunId}/payslips`,
    );
    const {total, isLoading} = usePaginate(payslipHttp, {
      normalizer: dataNormalizer,
    });

    return {
      runData,
      runHttp,
      payslipHttp,
      isLoading,
      total,
      items: usePaginate(payslipHttp, {normalizer: dataNormalizer}).response,
    };
  },
  data() {
    return {
      rejectionNote: '',
      emailSending: false,
      emailResult: null,
      headers: [
        {name: 'employeeName', title: 'Employee', style: {flex: 2}},
        {name: 'basicSalary', title: 'Basic', style: {flex: 1}},
        {name: 'grossSalary', title: 'Gross', style: {flex: 1}},
        {name: 'deductions', title: 'Deductions', style: {flex: 1}},
        {name: 'netSalary', title: 'Net Pay', style: {flex: 1}},
        {name: 'daysPresent', title: 'Present', style: {flex: 1}},
        {name: 'daysAbsent', title: 'Absent', style: {flex: 1}},
        {
          name: 'actions',
          title: '',
          slot: 'action',
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            view: {
              onClick: this.onClickViewPayslip,
              props: {name: 'eye-fill'},
            },
          },
        },
      ],
    };
  },
  methods: {
    formatAmount(amount) {
      return Number(amount || 0).toLocaleString();
    },
    onClickViewPayslip(item) {
      navigate('/payroll/payslip/{id}', {id: item.id});
    },
    async onApprove() {
      try {
        const approvalHttp = new APIService(
          window.appGlobal.baseUrl,
          `/api/v2/payroll/runs/${this.payrollRunId}/approve`,
        );
        await approvalHttp.update(this.payrollRunId, {
          action: 'approve',
          note: this.rejectionNote,
        });
        this.$toast.saveSuccess();
        navigate('/payroll/payrollRuns');
      } catch (error) {
        this.$toast.error({title: 'Error', message: 'Approval failed'});
      }
    },
    async onReject() {
      if (!this.rejectionNote) {
        this.$toast.warn({title: 'Warning', message: 'Please add a rejection note'});
        return;
      }
      try {
        const approvalHttp = new APIService(
          window.appGlobal.baseUrl,
          `/api/v2/payroll/runs/${this.payrollRunId}/approve`,
        );
        await approvalHttp.update(this.payrollRunId, {
          action: 'reject',
          note: this.rejectionNote,
        });
        this.$toast.saveSuccess();
        navigate('/payroll/payrollRuns');
      } catch (error) {
        this.$toast.error({title: 'Error', message: 'Rejection failed'});
      }
    },
    async onEmailPayslips() {
      this.emailSending = true;
      this.emailResult = null;
      try {
        const emailHttp = new APIService(
          window.appGlobal.baseUrl,
          '/api/v2/payroll/email-payslips',
        );
        const response = await emailHttp.create({runId: this.payrollRunId});
        this.emailResult = response.data?.data;
        this.$toast.success({title: 'Success', message: `${this.emailResult.sent} payslips emailed successfully`});
      } catch (error) {
        this.$toast.error({title: 'Error', message: 'Failed to email payslips'});
      } finally {
        this.emailSending = false;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.payroll-stats {
  margin: 1rem 0;
}
.stat-card {
  padding: 1rem;
  border-radius: 8px;
  background-color: #f6f6f6;
  text-align: center;
  &--net {
    background-color: #e8f5e9;
  }
}
.stat-label {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 0.5rem;
}
.stat-value {
  font-weight: 700;
  color: #1e293b;
}
</style>
