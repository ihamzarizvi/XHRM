<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">
          {{ isEdit ? 'Edit Item' : 'New Vault Entry' }}
        </h3>
        <button class="modal-close" @click="$emit('close')">&times;</button>
      </div>

      <div class="modal-body">
        <!-- Name & Type -->
        <div class="form-row">
          <div class="form-group flex-2">
            <label>Name</label>
            <input
              v-model="form.name"
              placeholder="e.g. GitHub, Work Email"
              class="custom-input"
            />
            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
          </div>
          <div class="form-group flex-1">
            <label>Type</label>
            <select v-model="form.itemType" class="custom-select">
              <option value="login">Login</option>
              <option value="card">Card</option>
              <option value="identity">Identity</option>
              <option value="note">Secure Note</option>
            </select>
          </div>
        </div>

        <!-- Login Specific -->
        <div v-if="form.itemType === 'login'" class="form-section">
          <div class="form-group">
            <label>Username / Email</label>
            <input v-model="form.username" class="custom-input" />
          </div>
          <div class="form-group">
            <label>Password</label>
            <div class="input-with-action">
              <input
                v-model="form.password"
                type="password"
                class="custom-input"
              />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group flex-1">
              <label>Website URL</label>
              <input
                v-model="form.url"
                placeholder="https://..."
                class="custom-input"
              />
            </div>
            <div class="form-group flex-1">
              <label>TOTP Secret (2FA Key)</label>
              <input
                v-model="form.totpSecret"
                placeholder="Optional"
                class="custom-input"
              />
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="form-group">
          <label>Notes</label>
          <textarea
            v-model="form.notes"
            rows="4"
            class="custom-textarea"
            placeholder="Additional details..."
          ></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-secondary" @click="$emit('close')">Cancel</button>
        <button class="btn-primary" @click="save">Save to Vault</button>
      </div>
    </div>
  </div>
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

<style lang="scss" scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  width: 100%;
  max-width: 650px;
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 25px 30px;
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a1a;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #999;
  cursor: pointer;
  &:hover {
    color: #ff5500;
  }
}

.modal-body {
  padding: 30px;
  max-height: 70vh;
  overflow-y: auto;
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.flex-1 {
  flex: 1;
}
.flex-2 {
  flex: 2;
}

.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;

  label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #555;
  }
}

.custom-input,
.custom-select,
.custom-textarea {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s;
  background: #fdfdfd;

  &:focus {
    outline: none;
    border-color: #ff5500;
    box-shadow: 0 0 0 4px rgba(255, 85, 0, 0.1);
  }
}

.modal-footer {
  padding: 20px 30px;
  background: #fafafa;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary {
  background: #ff5500;
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  &:hover {
    background: #e64a00;
  }
}

.btn-secondary {
  background: white;
  color: #555;
  border: 1px solid #e0e0e0;
  padding: 12px 25px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  &:hover {
    background: #f8f8f8;
  }
}

.error-text {
  color: #d32f2f;
  font-size: 0.8rem;
  margin-top: 4px;
}
</style>
