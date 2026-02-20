<template>
  <oxd-dialog :style="{maxWidth: '600px'}" @update:show="onClose">
    <div class="XHRM-dialog-container-default">
      <oxd-text tag="h6" class="XHRM-main-title">
        {{ isEditMode ? 'Edit Salary Component' : 'Add Salary Component' }}
      </oxd-text>
      <oxd-divider />
      <oxd-form ref="formRef" @submit-valid="onSubmit">
        <oxd-form-row>
          <oxd-grid :cols="2" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.name"
                label="Name"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.code"
                label="Code"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.type"
                type="select"
                label="Type"
                :options="typeOptions"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.calculationType"
                type="select"
                label="Calculation Type"
                :options="calcTypeOptions"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.defaultValue"
                label="Default Value (Amount or %)"
                type="input"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.formula"
                label="Formula (e.g., basic * 0.45)"
                type="input"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="3" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.isTaxable"
                type="checkbox"
                label="Taxable"
                :true-value="true"
                :false-value="false"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.isActive"
                type="checkbox"
                label="Active"
                :true-value="true"
                :false-value="false"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.sortOrder"
                label="Sort Order"
                type="input"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions>
          <oxd-button display-type="ghost" label="Cancel" @click="onClose" />
          <oxd-button
            class="XHRM-left-space"
            display-type="secondary"
            :label="isEditMode ? 'Update' : 'Save'"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </oxd-dialog>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  props: {
    data: {
      type: Object,
      default: null,
    },
  },
  emits: ['close', 'save'],
  data() {
    const isEdit = !!this.data;
    return {
      isEditMode: isEdit,
      form: {
        name: this.data?.name || '',
        code: this.data?.code || '',
        type: this.data?.type
          ? {id: this.data.type.toLowerCase(), label: this.data.type}
          : null,
        calculationType: null,
        defaultValue: this.data?.defaultValue || '',
        formula: this.data?.formula || '',
        isTaxable: this.data?.isTaxable ?? true,
        isActive: this.data?.isActive ?? true,
        sortOrder: this.data?.sortOrder || 0,
      },
      typeOptions: [
        {id: 'earning', label: 'Earning'},
        {id: 'deduction', label: 'Deduction'},
      ],
      calcTypeOptions: [
        {id: 'fixed', label: 'Fixed Amount'},
        {id: 'percentage', label: 'Percentage of Basic'},
        {id: 'formula', label: 'Formula'},
        {id: 'auto', label: 'Auto-calculated'},
      ],
      rules: {
        required: [(v) => (!!v && v !== '') || 'Required'],
      },
    };
  },
  methods: {
    onClose() {
      this.$emit('close');
    },
    async onSubmit() {
      const http = new APIService(
        window.appGlobal.baseUrl,
        '/api/v2/payroll/salary-components',
      );
      const payload = {
        name: this.form.name,
        code: this.form.code,
        type: this.form.type?.id,
        calculationType: this.form.calculationType?.id,
        defaultValue: this.form.defaultValue || null,
        formula: this.form.formula || null,
        isTaxable: this.form.isTaxable,
        isActive: this.form.isActive,
        sortOrder: parseInt(this.form.sortOrder) || 0,
      };

      try {
        if (this.isEditMode) {
          await http.update(this.data.id, payload);
        } else {
          await http.create(payload);
        }
        this.$toast.saveSuccess();
        this.$emit('save');
      } catch (error) {
        this.$toast.error({
          title: 'Error',
          message: 'Failed to save component',
        });
      }
    },
  },
};
</script>
