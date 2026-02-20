<template>
  <div class="XHRM-background-container">
    <div class="XHRM-paper-container">
      <div class="XHRM-header-container">
        <oxd-text tag="h6" class="XHRM-main-title">Generate Payroll</oxd-text>
      </div>
      <oxd-divider />
      <oxd-form ref="payrollForm" @submit-valid="onSubmit">
        <oxd-form-row>
          <oxd-grid :cols="3" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payroll.periodType"
                type="select"
                label="Pay Period"
                :rules="rules.required"
                :options="periodTypes"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payroll.periodStart"
                type="date"
                label="Period Start"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="payroll.periodEnd"
                type="date"
                label="Period End"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="3" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="payroll.currency"
                type="select"
                label="Currency"
                :options="currencies"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <div class="XHRM-payroll-summary" v-if="previewData">
          <oxd-text tag="h6" class="XHRM-main-title">Preview Summary</oxd-text>
          <oxd-grid :cols="4" class="XHRM-full-width-grid payroll-stats">
            <oxd-grid-item>
              <div class="stat-card">
                <oxd-text tag="p" class="stat-label">Employees</oxd-text>
                <oxd-text tag="h5" class="stat-value">{{
                  previewData.employeeCount
                }}</oxd-text>
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="stat-card">
                <oxd-text tag="p" class="stat-label">Total Gross</oxd-text>
                <oxd-text tag="h5" class="stat-value"
                  >PKR {{ formatAmount(previewData.totalGross) }}</oxd-text
                >
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="stat-card">
                <oxd-text tag="p" class="stat-label">Total Deductions</oxd-text>
                <oxd-text tag="h5" class="stat-value"
                  >PKR {{ formatAmount(previewData.totalDeductions) }}</oxd-text
                >
              </div>
            </oxd-grid-item>
            <oxd-grid-item>
              <div class="stat-card stat-card--net">
                <oxd-text tag="p" class="stat-label">Total Net Pay</oxd-text>
                <oxd-text tag="h5" class="stat-value"
                  >PKR {{ formatAmount(previewData.totalNet) }}</oxd-text
                >
              </div>
            </oxd-grid-item>
          </oxd-grid>
        </div>

        <oxd-form-actions>
          <oxd-button display-type="ghost" label="Preview" @click="onPreview" />
          <oxd-button
            class="XHRM-left-space"
            display-type="secondary"
            label="Generate Payroll"
            type="submit"
            :disabled="isGenerating"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {ref} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import {navigate} from '@/core/util/helper/navigation';

export default {
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/payroll/runs',
    );
    return {http};
  },
  data() {
    return {
      isGenerating: false,
      previewData: null,
      payroll: {
        periodType: null,
        periodStart: '',
        periodEnd: '',
        currency: {id: 'PKR', label: 'PKR - Pakistani Rupee'},
      },
      periodTypes: [
        {id: 'monthly', label: 'Monthly'},
        {id: 'biweekly', label: 'Bi-weekly'},
        {id: 'weekly', label: 'Weekly'},
        {id: 'contract', label: 'Contract'},
        {id: 'hourly', label: 'Hourly'},
      ],
      currencies: [
        {id: 'PKR', label: 'PKR - Pakistani Rupee'},
        {id: 'USD', label: 'USD - US Dollar'},
        {id: 'EUR', label: 'EUR - Euro'},
        {id: 'GBP', label: 'GBP - British Pound'},
        {id: 'AED', label: 'AED - UAE Dirham'},
        {id: 'SAR', label: 'SAR - Saudi Riyal'},
      ],
      rules: {
        required: [(v) => (!!v && v !== '') || 'Required'],
      },
    };
  },
  methods: {
    formatAmount(amount) {
      return Number(amount || 0).toLocaleString();
    },
    async onPreview() {
      // TODO: Call preview API
      this.previewData = {
        employeeCount: 0,
        totalGross: 0,
        totalDeductions: 0,
        totalNet: 0,
      };
    },
    async onSubmit() {
      this.isGenerating = true;
      try {
        await this.http.create({
          periodType: this.payroll.periodType?.id,
          periodStart: this.payroll.periodStart,
          periodEnd: this.payroll.periodEnd,
          currencyId: this.payroll.currency?.id || 'PKR',
        });
        this.$toast.saveSuccess();
        navigate('/payroll/payrollRuns');
      } catch (error) {
        this.$toast.error({
          title: 'Error',
          message: 'Failed to generate payroll',
        });
      } finally {
        this.isGenerating = false;
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
