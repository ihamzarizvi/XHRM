<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <div class="header-main">
          <div class="header-icon">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <h3 class="modal-title">
            {{ isEdit ? 'Edit Vault Entry' : 'New Vault Entry' }}
          </h3>
        </div>
        <button class="modal-close" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="modal-body">
        <!-- Quick Settings -->
        <div class="form-row">
          <div class="form-group flex-2">
            <label class="premium-label">Item Name</label>
            <div class="input-wrapper">
              <i class="bi bi-tag-fill input-icon"></i>
              <input
                v-model="form.name"
                placeholder="e.g. My GitHub Account"
                class="premium-input"
              />
            </div>
            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
          </div>
          <div class="form-group flex-1">
            <label class="premium-label">Type</label>
            <div class="input-wrapper">
              <i class="bi bi-grid-fill input-icon"></i>
              <select
                v-model="form.itemType"
                class="premium-input premium-select"
              >
                <option value="login">Login</option>
                <option value="card">Card</option>
                <option value="identity">Identity</option>
                <option value="note">Secure Note</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Login Details Section -->
        <div v-if="form.itemType === 'login'" class="premium-section">
          <div class="section-title">Login Credentials</div>

          <div class="form-group">
            <label class="premium-label">Username / Email</label>
            <div class="input-wrapper">
              <i class="bi bi-person-fill input-icon"></i>
              <input
                v-model="form.username"
                class="premium-input"
                placeholder="john.doe@example.com"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="premium-label">Password</label>
            <div class="input-wrapper">
              <i class="bi bi-key-fill input-icon"></i>
              <input
                v-model="form.password"
                type="password"
                class="premium-input"
                placeholder="••••••••••••"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group flex-1">
              <label class="premium-label">Website URL</label>
              <div class="input-wrapper">
                <i class="bi bi-link-45deg input-icon"></i>
                <input
                  v-model="form.url"
                  placeholder="https://github.com"
                  class="premium-input"
                  @blur="normalizeUrl"
                />
              </div>
            </div>
            <div class="form-group flex-1">
              <label class="premium-label">2FA Secret Key</label>
              <div class="input-wrapper">
                <i class="bi bi-clock-fill input-icon"></i>
                <input
                  v-model="form.totpSecret"
                  placeholder="Optional"
                  class="premium-input"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="form-group mt-20">
          <label class="premium-label">Additional Notes</label>
          <div class="input-wrapper">
            <textarea
              v-model="form.notes"
              rows="3"
              class="premium-input premium-textarea"
              placeholder="Store any extra details or recovery codes here..."
            ></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button
          class="btn-cancel"
          :disabled="isLoading"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button class="btn-save" :disabled="isLoading" @click="save">
          <i v-if="isLoading" class="bi bi-arrow-repeat spin"></i>
          <i v-else class="bi bi-check2-circle"></i>
          {{ isLoading ? 'Saving...' : 'Save to Vault' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, watch, PropType} from 'vue';
import {SecurityService} from '../services/SecurityService';
import {TOTPService} from '../services/TOTPService';

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
    isLoading: {type: Boolean, default: false},
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
      totpSecret: '',
    });

    const errors = ref<Record<string, string>>({});
    const isEdit = ref(false);
    const totpPreview = ref<string | null>(null);

    watch(
      () => props.item,
      async (newItem) => {
        if (newItem) {
          try {
            // Use the item's decrypted key if available (set during list fetch),
            // otherwise fall back to master key (legacy items).
            const itemKey = newItem._decryptedItemKey || undefined;

            const [username, password, url, notes, secret] = await Promise.all([
              newItem.usernameEncrypted
                ? SecurityService.decrypt(newItem.usernameEncrypted, itemKey)
                : '',
              newItem.passwordEncrypted
                ? SecurityService.decrypt(newItem.passwordEncrypted, itemKey)
                : '',
              newItem.urlEncrypted
                ? SecurityService.decrypt(newItem.urlEncrypted, itemKey)
                : '',
              newItem.notesEncrypted
                ? SecurityService.decrypt(newItem.notesEncrypted, itemKey)
                : '',
              newItem.totpSecretEncrypted
                ? SecurityService.decrypt(newItem.totpSecretEncrypted, itemKey)
                : '',
            ]);

            form.value = {
              ...newItem,
              username,
              password,
              url,
              notes,
              totpSecret: secret,
            };
            isEdit.value = true;
          } catch (e) {
            console.error('Failed to decrypt item for editing', e);
            alert('Failed to decrypt item. Ensure your vault is unlocked.');
          }
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

    // Watch for TOTP secret changes to show preview
    watch(
      () => form.value.totpSecret,
      (newSecret) => {
        if (
          newSecret &&
          newSecret.length >= 16 &&
          newSecret !== '[Encrypted Data]'
        ) {
          totpPreview.value = TOTPService.generateCode(newSecret);
        } else {
          totpPreview.value = null;
        }
      },
    );

    const normalizeUrl = () => {
      if (form.value.url && !form.value.url.match(/^https?:\/\//i)) {
        form.value.url = 'https://' + form.value.url;
      }
    };

    const save = async () => {
      if (!form.value.name) {
        errors.value = {name: 'Name is required'};
        return;
      }

      normalizeUrl();

      try {
        // --- Per-Item Key Management ---
        // Each item gets its own AES-256-GCM key.
        // This item key is encrypted with the user's master key and stored.
        // When sharing, the item key is re-encrypted with the recipient's RSA public key.

        let itemKey: CryptoKey;

        if (isEdit.value && props.item?.encryptedItemKey) {
          // Existing item: decrypt and reuse its item key
          const itemKeyRaw = await SecurityService.decrypt(
            props.item.encryptedItemKey,
          );
          if (itemKeyRaw && itemKeyRaw !== '[Encrypted Data]') {
            itemKey = await SecurityService.importAESKey(itemKeyRaw);
          } else {
            // Can't decrypt existing key, generate new one (key rotation)
            console.warn(
              'Could not decrypt existing item key, generating new one',
            );
            itemKey = await SecurityService.generateAESKey();
          }
        } else {
          // New item or legacy item without key: generate fresh
          itemKey = await SecurityService.generateAESKey();
        }

        // Export and encrypt the item key with the master key
        const itemKeyRaw = await SecurityService.exportAESKey(itemKey);
        const encryptedItemKey = await SecurityService.encrypt(itemKeyRaw);

        // Encrypt all fields with the item key
        const [usernameEnc, passwordEnc, urlEnc, notesEnc, totpSecretEnc] =
          await Promise.all([
            SecurityService.encrypt(form.value.username || '', itemKey),
            SecurityService.encrypt(form.value.password || '', itemKey),
            SecurityService.encrypt(form.value.url || '', itemKey),
            SecurityService.encrypt(form.value.notes || '', itemKey),
            SecurityService.encrypt(form.value.totpSecret || '', itemKey),
          ]);

        const output: any = {
          name: form.value.name,
          itemType: form.value.itemType,
          encryptedItemKey: encryptedItemKey,
          usernameEncrypted: usernameEnc,
          passwordEncrypted: passwordEnc,
          urlEncrypted: urlEnc,
          notesEncrypted: notesEnc,
          totpSecretEncrypted: totpSecretEnc,
        };

        if (form.value.id) {
          output.id = form.value.id;
        }

        emit('save', output);
      } catch (e) {
        console.error('VaultItemForm: encryption failed', e);
        alert(
          'Encryption failed. Please unlock your vault first before saving items.',
        );
        return; // CRITICAL: Abort save to prevent corrupted data
      }
    };

    return {form, errors, isEdit, save, totpPreview, normalizeUrl};
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
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-content {
  background: #ffffff;
  width: 100%;
  max-width: 680px;
  border-radius: 24px;
  box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: modalEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalEnter {
  from {
    transform: scale(0.95) translateY(30px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 24px 32px;
  background: #fff;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;

  .header-main {
    display: flex;
    align-items: center;
    gap: 16px;

    .header-icon {
      width: 44px;
      height: 44px;
      background: #fff0e6;
      color: #ff5500;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
    }
  }
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
}

.modal-close {
  background: #f8fafc;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  &:hover {
    background: #fee2e2;
    color: #ef4444;
  }
}

.modal-body {
  padding: 32px;
  max-height: calc(90vh - 160px);
  overflow-y: auto;

  &::-webkit-scrollbar {
    width: 6px;
  }
  &::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
  }
}

.premium-section {
  background: #f8fafc;
  border-radius: 16px;
  padding: 24px;
  margin-top: 24px;
  border: 1px solid #e2e8f0;

  .section-title {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.05em;
    margin-bottom: 16px;
  }
}

.form-row {
  display: flex;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
  flex: 1;
}

.premium-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;

  .input-icon {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 1rem;
    pointer-events: none;
  }
}

.premium-input {
  width: 100%;
  padding: 12px 14px 12px 42px;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #1e293b;
  transition: all 0.2s;

  &::placeholder {
    color: #cbd5e1;
  }

  &:focus {
    outline: none;
    border-color: #ff5500;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(255, 85, 0, 0.1);
  }
}

.premium-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 16px;
}

.premium-textarea {
  padding-left: 14px;
  min-height: 100px;
  resize: vertical;
}

.modal-footer {
  padding: 24px 32px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-save {
  background: linear-gradient(135deg, #ff7b00 0%, #ff5500 100%);
  color: white;
  border: none;
  padding: 14px 28px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(255, 85, 0, 0.3);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 85, 0, 0.4);
  }

  i {
    font-size: 1.1rem;
  }
}

.btn-cancel {
  background: white;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  padding: 14px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  &:hover {
    background: #f1f5f9;
    color: #0f172a;
  }
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.error-text {
  color: #ef4444;
  font-size: 0.75rem;
  font-weight: 600;
  margin-top: 6px;
}

.mt-20 {
  margin-top: 20px;
}
.flex-1 {
  flex: 1;
}
.flex-2 {
  flex: 2;
}
</style>
