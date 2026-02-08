<template>
  <oxd-dialog :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="oxd-text oxd-text--h3">
        {{ isEdit ? 'Edit Item' : 'Add Item' }}
      </h3>
    </template>

    <div class="oxd-form-row">
      <div class="oxd-input-group oxd-input-field-bottom-space">
        <label class="oxd-label oxd-label--active">Name</label>
        <input
          v-model="form.name"
          class="oxd-input oxd-input--active"
          required
        />
        <span
          v-if="errors.name"
          class="oxd-text oxd-text--span oxd-input-field-error-message oxd-input-group__message"
          >{{ errors.name }}</span
        >
      </div>
    </div>

    <div class="oxd-form-row">
      <div class="oxd-input-group oxd-input-field-bottom-space">
        <label class="oxd-label oxd-label--active">Type</label>
        <select v-model="form.itemType" class="oxd-select-wrapper">
          <option value="login">Login</option>
          <option value="card">Card</option>
          <option value="identity">Identity</option>
          <option value="note">Secure Note</option>
        </select>
      </div>
    </div>

    <div v-if="form.itemType === 'login'">
      <div class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">Username</label>
          <input v-model="form.username" class="oxd-input oxd-input--active" />
        </div>
      </div>
      <div class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="oxd-input oxd-input--active"
          />
        </div>
      </div>
      <div class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">URL</label>
          <input v-model="form.url" class="oxd-input oxd-input--active" />
        </div>
      </div>
      <div class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">TOTP Secret (Key)</label>
          <input
            v-model="form.totpSecret"
            class="oxd-input oxd-input--active"
          />
        </div>
      </div>
    </div>

    <div class="oxd-form-row">
      <div class="oxd-input-group oxd-input-field-bottom-space">
        <label class="oxd-label">Notes</label>
        <textarea
          v-model="form.notes"
          class="oxd-input oxd-input--active"
          rows="3"
        ></textarea>
      </div>
    </div>

    <template #footer>
      <button
        class="oxd-button oxd-button--medium oxd-button--ghost"
        @click="$emit('close')"
      >
        Cancel
      </button>
      <button
        class="oxd-button oxd-button--medium oxd-button--main"
        @click="save"
      >
        Save
      </button>
    </template>
  </oxd-dialog>
</template>

<script lang="ts">
import {defineComponent, ref, watch, PropType} from 'vue';
import {SecurityService} from '../services/SecurityService';

interface VaultItemFormData {
  id?: number;
  name: string;
  itemType: string;
  username: string;
  password?: string;
  url?: string;
  notes?: string;
  totpSecret?: string;
}

export default defineComponent({
  name: 'VaultItemForm',
  props: {
    isOpen: {type: Boolean, required: true},
    item: {type: Object as PropType<any>, default: null},
  },
  emits: ['close', 'save'],
  setup(props, {emit}) {
    const form = ref<VaultItemFormData>({
      name: '',
      itemType: 'login',
      username: '',
      password: '',
      url: '',
      notes: '',
    });

    const errors = ref<Record<string, string>>({});
    const isEdit = ref(false);

    watch(
      () => props.item,
      (newItem) => {
        if (newItem) {
          form.value = {...newItem};
          if (newItem.usernameEncrypted)
            form.value.username = SecurityService.decrypt(
              newItem.usernameEncrypted,
            );
          if (newItem.passwordEncrypted)
            form.value.password = SecurityService.decrypt(
              newItem.passwordEncrypted,
            );
          if (newItem.urlEncrypted)
            form.value.url = SecurityService.decrypt(newItem.urlEncrypted);
          if (newItem.notesEncrypted)
            form.value.notes = SecurityService.decrypt(newItem.notesEncrypted);
          if (newItem.totpSecretEncrypted)
            form.value.totpSecret = SecurityService.decrypt(
              newItem.totpSecretEncrypted,
            );
          isEdit.value = true;
        } else {
          form.value = {
            name: '',
            itemType: 'login',
            username: '',
            password: '',
            url: '',
            notes: '',
            totpSecret: '',
          };
          isEdit.value = false;
        }
      },
      {immediate: true},
    );

    const save = () => {
      if (!form.value.name) {
        errors.value = {name: 'Name is required'};
        return;
      }

      const output = {
        ...form.value,
        usernameEncrypted: SecurityService.encrypt(form.value.username || ''),
        passwordEncrypted: SecurityService.encrypt(form.value.password || ''),
        urlEncrypted: SecurityService.encrypt(form.value.url || ''),
        notesEncrypted: SecurityService.encrypt(form.value.notes || ''),
        totpSecretEncrypted: SecurityService.encrypt(
          form.value.totpSecret || '',
        ),
      };

      emit('save', output);
    };

    return {form, errors, isEdit, save};
  },
});
</script>
