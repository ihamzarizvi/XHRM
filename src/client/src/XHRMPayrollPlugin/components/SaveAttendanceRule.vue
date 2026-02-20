<template>
  <oxd-dialog
    :style="{maxWidth: '500px'}"
    @update:show="onClose"
  >
    <div class="XHRM-dialog-container-default">
      <oxd-text tag="h6" class="XHRM-main-title">
        {{ isEditMode ? 'Edit Attendance Rule' : 'Add Attendance Rule' }}
      </oxd-text>
      <oxd-divider />
      <oxd-form ref="formRef" @submit-valid="onSubmit">
        <oxd-form-row>
          <oxd-input-field
            v-model="form.name"
            label="Rule Name"
            :rules="rules.required"
            required
          />
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="XHRM-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.gracePeriodMinutes"
                label="Grace Period (minutes)"
                type="input"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.halfDayHours"
                label="Half Day Threshold (hours)"
                type="input"
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
                v-model="form.latesPerAbsent"
                label="Lates per Absent"
                type="input"
                :rules="rules.required"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="form.isDefault"
                type="checkbox"
                label="Default Rule"
                :true-value="true"
                :false-value="false"
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
    data: {type: Object, default: null},
  },
  emits: ['close', 'save'],
  data() {
    return {
      isEditMode: !!this.data,
      form: {
        name: this.data?.name || '',
        gracePeriodMinutes: this.data?.gracePeriodMinutes || 15,
        halfDayHours: this.data?.halfDayHours || 4,
        latesPerAbsent: this.data?.latesPerAbsent || 3,
        isDefault: this.data?.isDefault ?? true,
      },
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
        '/api/v2/payroll/attendance-rules',
      );
      const payload = {
        name: this.form.name,
        gracePeriodMinutes: parseInt(this.form.gracePeriodMinutes),
        halfDayHours: parseFloat(this.form.halfDayHours),
        latesPerAbsent: parseInt(this.form.latesPerAbsent),
        isDefault: this.form.isDefault,
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
        this.$toast.error({title: 'Error', message: 'Failed to save rule'});
      }
    },
  },
};
</script>
