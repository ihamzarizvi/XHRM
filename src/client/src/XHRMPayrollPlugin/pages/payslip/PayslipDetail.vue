<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container payslip-detail">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Payslip Detail</oxd-text>
        <oxd-button
          display-type="ghost"
          label="Download PDF"
          icon-name="download"
          @click="onDownloadPdf"
        />
      </div>

      <div v-if="payslip" class="payslip-content">
        <!-- Employee Info -->
        <div class="payslip-section">
          <oxd-text tag="h6" class="section-title">Employee Information</oxd-text>
          <oxd-grid :cols="3" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <div class="info-row">
                <oxd-text tag="p" class="info-label">Name</oxd-text>
                <oxd-text tag="p" class="info-value">
                  {{ payslip.employee?.firstName }} {{ payslip.employee?.lastName }}
                </oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="info-row">
                <oxd-text tag="p" class="info-label">Employee ID</oxd-text>
                <oxd-text tag="p" class="info-value">{{ payslip.employee?.employeeId }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="info-row">
                <oxd-text tag="p" class="info-label">Pay Period</oxd-text>
                <oxd-text tag="p" class="info-value">{{ payslip.payPeriodType }}</oxd-text>
              </div>
            </oxd-grid-item>
          </oxd-grid>
        </div>

        <oxd-divider />

        <!-- Attendance Summary -->
        <div class="payslip-section">
          <oxd-text tag="h6" class="section-title">Attendance Summary</oxd-text>
          <oxd-grid :cols="6" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">Working Days</oxd-text>
                <oxd-text tag="h6" class="att-value">{{ payslip.totalWorkingDays }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">Present</oxd-text>
                <oxd-text tag="h6" class="att-value att-value--good">{{ payslip.daysPresent }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">Absent</oxd-text>
                <oxd-text tag="h6" class="att-value att-value--bad">{{ payslip.daysAbsent }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">Leaves</oxd-text>
                <oxd-text tag="h6" class="att-value">{{ payslip.daysLeave }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">Late</oxd-text>
                <oxd-text tag="h6" class="att-value att-value--warn">{{ payslip.lateCount }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="att-stat">
                <oxd-text tag="p" class="att-label">OT Hours</oxd-text>
                <oxd-text tag="h6" class="att-value">{{ payslip.overtimeHours }}</oxd-text>
              </div>
            </oxd-grid-item>
          </oxd-grid>
        </div>

        <oxd-divider />

        <!-- Earnings & Deductions -->
        <oxd-grid :cols="2" class="XHRM-full-width-grid">
          <oxd-grid-item>
            <div class="payslip-section">
              <oxd-text tag="h6" class="section-title section-title--earn">Earnings</oxd-text>
              <div
                v-for="item in earnings"
                :key="item.id"
                class="line-item"
              >
                <oxd-text tag="p">{{ item.name }}</oxd-text>
                <oxd-text tag="p" class="line-amount">PKR {{ formatAmount(item.amount) }}</oxd-text>
              </div>
              <oxd-divider />
              <div class="line-item line-item--total">
                <oxd-text tag="p"><strong>Gross Salary</strong></oxd-text>
                <oxd-text tag="p" class="line-amount"><strong>PKR {{ formatAmount(payslip.grossSalary) }}</strong></oxd-text>
              </div>
            </div>
          </oxd-grid-item>
          <oxd-grid-item>
            <div class="payslip-section">
              <oxd-text tag="h6" class="section-title section-title--deduct">Deductions</oxd-text>
              <div
                v-for="item in deductions"
                :key="item.id"
                class="line-item"
              >
                <oxd-text tag="p">{{ item.name }}</oxd-text>
                <oxd-text tag="p" class="line-amount">PKR {{ formatAmount(item.amount) }}</oxd-text>
              </div>
              <oxd-divider />
              <div class="line-item line-item--total">
                <oxd-text tag="p"><strong>Total Deductions</strong></oxd-text>
                <oxd-text tag="p" class="line-amount"><strong>PKR {{ formatAmount(payslip.totalDeductions) }}</strong></oxd-text>
              </div>
            </div>
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />

        <!-- Net Pay -->
        <div class="net-pay-section">
          <oxd-text tag="h5" class="net-pay-label">Net Pay</oxd-text>
          <oxd-text tag="h4" class="net-pay-amount">PKR {{ formatAmount(payslip.netSalary) }}</oxd-text>
        </div>
      </div>

      <div v-else class="loading-container">
        <oxd-text tag="p">Loading payslip...</oxd-text>
      </div>
    </div>
  </div>
</template>

<script>
import {ref, onMounted, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';

export default {
  props: {
    payslipId: {
      type: Number,
      required: true,
    },
  },
  setup(props) {
    const payslip = ref(null);
    const lineItems = ref([]);

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/payslips',
    );

    onMounted(async () => {
      try {
        const response = await http.get(props.payslipId);
        payslip.value = response.data?.data;
        lineItems.value = payslip.value?.items || [];
      } catch (error) {
        console.error('Failed to load payslip:', error);
      }
    });

    const earnings = computed(() => lineItems.value.filter((i) => i.type === 'earning'));
    const deductions = computed(() => lineItems.value.filter((i) => i.type === 'deduction'));

    return {payslip, lineItems, earnings, deductions, http};
  },
  methods: {
    formatAmount(amount) {
      return Number(amount || 0).toLocaleString();
    },
    onDownloadPdf() {
      window.open(
        `${window.appGlobal.baseUrl}/payroll/payslip/${this.payslipId}/pdf`,
        '_blank',
      );
    },
  },
};
</script>

<style lang="scss" scoped>
.payslip-detail {
  max-width: 900px;
  margin: 0 auto;
}
.payslip-section {
  margin-bottom: 1rem;
}
.section-title {
  margin-bottom: 0.75rem;
  color: #1e293b;
  &--earn { color: #16a34a; }
  &--deduct { color: #dc2626; }
}
.info-row {
  margin-bottom: 0.5rem;
}
.info-label {
  font-size: 0.8rem;
  color: #64748b;
}
.info-value {
  font-weight: 600;
  color: #1e293b;
}
.att-stat {
  text-align: center;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
}
.att-label {
  font-size: 0.8rem;
  color: #64748b;
}
.att-value {
  font-weight: 700;
  &--good { color: #16a34a; }
  &--bad { color: #dc2626; }
  &--warn { color: #f59e0b; }
}
.line-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  &--total {
    padding-top: 0.5rem;
  }
}
.line-amount {
  text-align: right;
}
.net-pay-section {
  text-align: center;
  padding: 1.5rem;
  background: #e8f5e9;
  border-radius: 12px;
  margin-top: 1rem;
}
.net-pay-label {
  color: #64748b;
  margin-bottom: 0.5rem;
}
.net-pay-amount {
  color: #16a34a;
  font-weight: 800;
  font-size: 1.75rem;
}
</style>
