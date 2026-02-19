<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">View Login</h3>
        <button class="modal-close" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="modal-body">
        <!-- Header Card -->
        <div class="item-header-card">
          <div class="item-icon-large" :class="item.itemType">
            <img
              v-if="faviconUrl"
              :src="faviconUrl"
              class="item-favicon"
              @error="faviconError = true"
            />
            <i v-else :class="getItemIcon(item.itemType)"></i>
          </div>
          <div class="item-header-info">
            <div class="item-name">{{ item.name }}</div>
            <div class="item-category">
              <i class="bi bi-folder"></i>
              {{ categoryName || 'Uncategorized' }}
            </div>
          </div>
        </div>

        <!-- Login Credentials Section -->
        <div class="section-container">
          <div class="section-title">Login credentials</div>
          <div class="fields-group">
            <!-- Username -->
            <div class="field-row">
              <label>Username</label>
              <div class="field-value-container">
                <div class="field-value">{{ decryptedData.username }}</div>
                <button
                  class="action-btn"
                  title="Copy Username"
                  @click="copyToClipboard(decryptedData.username)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>
            </div>

            <!-- Password -->
            <div class="field-row">
              <label>Password</label>
              <div class="field-value-container">
                <div class="field-value password">
                  {{
                    showPassword ? decryptedData.password : '••••••••••••••••'
                  }}
                </div>
                <div class="field-actions">
                  <button
                    class="action-btn"
                    title="Toggle Visibility"
                    @click="showPassword = !showPassword"
                  >
                    <i
                      class="bi"
                      :class="showPassword ? 'bi-eye' : 'bi-eye-slash'"
                    ></i>
                  </button>
                  <button
                    class="action-btn"
                    title="Copy Password"
                    @click="copyToClipboard(decryptedData.password)"
                  >
                    <i class="bi bi-copy"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- TOTP -->
            <div v-if="decryptedData.totpSecret" class="field-row">
              <label>Verification Code (TOTP)</label>
              <div class="field-value-container">
                <div class="totp-display">
                  <span class="totp-code">{{
                    decryptedData.totpSecret === '[Encrypted Data]'
                      ? 'Unavailable'
                      : currentTotp || 'Generating...'
                  }}</span>
                  <div class="totp-circle-container">
                    <svg class="totp-circle" viewBox="0 0 36 36">
                      <circle class="totp-circle-bg" cx="18" cy="18" r="15.9" />
                      <circle
                        class="totp-circle-fg"
                        cx="18"
                        cy="18"
                        r="15.9"
                        :style="{strokeDashoffset: 100 - timerWidth + '%'}"
                      />
                    </svg>
                    <span class="totp-seconds">{{ remainingSeconds }}</span>
                  </div>
                </div>
                <button
                  class="action-btn"
                  title="Copy Code"
                  @click="copyToClipboard(currentTotp)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>
            </div>
            <div v-else class="field-row empty">
              <label>Verification Code (TOTP)</label>
              <div class="setup-totp">
                <i class="bi bi-shield-lock"></i> Not configured
              </div>
            </div>
          </div>
        </div>

        <!-- Website Section -->
        <div class="section-container">
          <div class="section-title">Autofill options</div>
          <div class="fields-group">
            <div class="field-row">
              <label>Website</label>
              <div class="field-value-container">
                <div class="field-value url">
                  {{ decryptedData.url || 'No URL configured' }}
                </div>
                <div v-if="decryptedData.url" class="field-actions">
                  <a
                    :href="normalizeUrl(decryptedData.url)"
                    target="_blank"
                    class="action-btn"
                    title="Open Website"
                  >
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                  <button
                    class="action-btn"
                    title="Copy URL"
                    @click="copyToClipboard(decryptedData.url)"
                  >
                    <i class="bi bi-copy"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="decryptedData.notes" class="section-container">
          <div class="section-title">Additional options</div>
          <div class="fields-group">
            <div class="field-row vertical">
              <label>Note</label>
              <div class="field-value-container">
                <div class="field-value note-text">
                  {{ decryptedData.notes }}
                </div>
                <button
                  class="action-btn top-right"
                  title="Copy Note"
                  @click="copyToClipboard(decryptedData.notes)"
                >
                  <i class="bi bi-copy"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- History -->
        <div class="section-container">
          <div class="section-title">Item history</div>
          <div class="history-box">
            <div class="history-item">
              Last edited: {{ formatDate(item.updatedAt || item.createdAt) }}
            </div>
            <div class="history-item">
              Created: {{ formatDate(item.createdAt) }}
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="footer-btn edit-btn" @click="$emit('edit', item)">
          Edit
        </button>
        <button
          class="footer-btn share-btn"
          title="Share this item"
          @click="$emit('share', item)"
        >
          <i class="bi bi-person-plus-fill"></i> Share
        </button>
        <button class="footer-btn delete-btn" @click="confirmDelete">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {
  defineComponent,
  ref,
  computed,
  onMounted,
  onUnmounted,
  PropType,
} from 'vue';
import {SecurityService} from '../services/SecurityService';
import {TOTPService} from '../services/TOTPService';
import {format} from 'date-fns';

export default defineComponent({
  name: 'VaultItemView',
  props: {
    isOpen: {type: Boolean, required: true},
    item: {type: Object as PropType<any>, required: true},
    categoryName: {type: String, default: ''},
  },
  emits: ['close', 'edit', 'delete', 'share'],
  setup(props, {emit}) {
    const decryptedData = ref({
      username: '',
      password: '',
      url: '',
      notes: '',
      totpSecret: '',
    });

    const showPassword = ref(false);
    const currentTotp = ref('');
    const timerWidth = ref(100);
    const remainingSeconds = ref(30);
    const faviconError = ref(false);
    let timerInterval: any = null;

    onMounted(async () => {
      await decryptItem();
      startTotpTimer();
    });

    onUnmounted(() => {
      if (timerInterval) clearInterval(timerInterval);
    });

    const decryptItem = async () => {
      if (!props.item) return;

      try {
        // Step 1: resolve the per-item AES key
        // Items are encrypted with a unique per-item key, which is itself
        // encrypted with the master key and stored as encryptedItemKey.
        let itemKey: CryptoKey | undefined = undefined;

        // If the item already has a pre-decrypted key (passed from the list), use it
        if (props.item._decryptedItemKey) {
          itemKey = props.item._decryptedItemKey;
        } else if (props.item.encryptedItemKey) {
          try {
            const itemKeyRaw = await SecurityService.decrypt(
              props.item.encryptedItemKey,
            );
            if (itemKeyRaw && itemKeyRaw !== '[Encrypted Data]') {
              itemKey = await SecurityService.importAESKey(itemKeyRaw);
            }
          } catch (e) {
            console.error('Failed to decrypt item key in view', e);
          }
        }

        // Step 2: decrypt all fields using the item key (falls back to master key if undefined)
        const [user, pass, url, notes, secret] = await Promise.all([
          props.item.usernameEncrypted
            ? SecurityService.decrypt(props.item.usernameEncrypted, itemKey)
            : '',
          props.item.passwordEncrypted
            ? SecurityService.decrypt(props.item.passwordEncrypted, itemKey)
            : '',
          props.item.urlEncrypted
            ? SecurityService.decrypt(props.item.urlEncrypted, itemKey)
            : '',
          props.item.notesEncrypted
            ? SecurityService.decrypt(props.item.notesEncrypted, itemKey)
            : '',
          props.item.totpSecretEncrypted
            ? SecurityService.decrypt(props.item.totpSecretEncrypted, itemKey)
            : '',
        ]);

        decryptedData.value = {
          username: user,
          password: pass,
          url: url,
          notes: notes,
          totpSecret: secret,
        };
      } catch (e) {
        console.error('Decryption failed', e);
      }
    };

    const startTotpTimer = () => {
      const update = () => {
        if (
          decryptedData.value.totpSecret &&
          decryptedData.value.totpSecret !== '[Encrypted Data]'
        ) {
          const secret = decryptedData.value.totpSecret;
          const code = TOTPService.generateCode(secret);
          if (code) currentTotp.value = code;

          const remaining = TOTPService.getRemainingSeconds();
          timerWidth.value = (remaining / 30) * 100;
          remainingSeconds.value = remaining;
        }
      };

      update(); // Initial call
      timerInterval = setInterval(update, 1000);
    };

    const copyToClipboard = async (text: string) => {
      if (!text) return;
      try {
        await navigator.clipboard.writeText(text);
        // Could show a toast here
      } catch (err) {
        console.error('Failed to copy', err);
      }
    };

    const normalizeUrl = (url: string) => {
      if (!url) return '';
      if (url.match(/^https?:\/\//i)) return url;
      return 'https://' + url;
    };

    const formatDate = (dateString: string) => {
      if (!dateString) return 'Unknown';
      try {
        // Handle potential different date formats from API (Doctrine object vs string)
        let dateVal = dateString;
        if (
          typeof dateString === 'object' &&
          dateString !== null &&
          (dateString as any).date
        ) {
          dateVal = (dateString as any).date;
        }

        const date = new Date(dateVal);
        if (isNaN(date.getTime())) {
          console.warn('Invalid date:', dateString);
          return 'Invalid Date';
        }
        return format(date, 'MMM d, yyyy, h:mm a');
      } catch (e) {
        console.error('Date parse error', e);
        return 'Error';
      }
    };

    const confirmDelete = () => {
      if (confirm('Are you sure you want to delete this item?')) {
        emit('delete', props.item);
      }
    };

    const getItemIcon = (type: string) => {
      switch (type) {
        case 'login':
          return 'oxd-icon bi-key';
        case 'card':
          return 'oxd-icon bi-credit-card';
        case 'note':
          return 'oxd-icon bi-journal-text';
        default:
          return 'oxd-icon bi-shield-lock';
      }
    };

    const faviconUrl = computed(() => {
      if (faviconError.value) return null;
      const url = decryptedData.value.url || props.item?.url;
      if (!url) return null;
      try {
        const normalized = normalizeUrl(url);
        const domain = new URL(normalized).hostname;
        return `https://www.google.com/s2/favicons?domain=${domain}&sz=64`;
      } catch (e) {
        return null;
      }
    });

    return {
      decryptedData,
      showPassword,
      currentTotp,
      timerWidth,
      remainingSeconds,
      faviconUrl,
      faviconError,
      copyToClipboard,
      normalizeUrl,
      formatDate,
      confirmDelete,
      getItemIcon,
    };
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
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #ffffff;
  width: 100%;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #1f2937;
  border: 1px solid #e5e7eb;
}

.modal-header {
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;

  .modal-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    color: #111827;
  }

  .modal-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    &:hover {
      background: #e5e7eb;
      color: #374151;
    }
  }
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  max-height: 80vh;
  background: #ffffff;
}

/* Header Card */
.item-header-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  border: 1px solid #e5e7eb;

  .item-icon-large {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: #ffffff;
    color: #6b7280;
    border: 1px solid #e5e7eb;
    overflow: hidden;

    .item-favicon {
      width: 32px;
      height: 32px;
      object-fit: contain;
    }
  }

  .item-header-info {
    .item-name {
      font-size: 1.1rem;
      font-weight: 600;
      color: #111827;
      margin-bottom: 4px;
    }
    .item-category {
      font-size: 0.85rem;
      color: #6b7280;
      display: flex;
      align-items: center;
      gap: 6px;
    }
  }
}

/* Sections */
.section-container {
  margin-bottom: 24px;

  .section-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
}

.fields-group {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.field-row {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 4px;
  position: relative;

  &:last-child {
    border-bottom: none;
  }

  &.vertical {
    align-items: flex-start;
  }

  label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
  }

  .field-value-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  .field-value {
    font-size: 0.95rem;
    color: #1f2937;
    font-weight: 500;
    word-break: break-all;
    flex: 1;

    &.password {
      font-family: monospace;
      letter-spacing: 1px;
    }

    &.url {
      color: #2563eb;
    }

    &.note-text {
      white-space: pre-wrap;
      font-size: 0.9rem;
      color: #4b5563;
      padding-right: 40px;
    }
  }

  .field-actions {
    display: flex;
    gap: 8px;
    margin-left: 12px;
  }

  .action-btn {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: all 0.2s;

    &:hover {
      background: #f3f4f6;
      color: var(--oxd-primary-one-color);
    }

    &.top-right {
      position: absolute;
      top: 12px;
      right: 12px;
    }
  }

  .setup-totp {
    font-size: 0.9rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
  }
}

/* TOTP Circular Timer */
.totp-display {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.totp-code {
  font-family: monospace;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--oxd-primary-one-color);
  letter-spacing: 2px;
}

.totp-circle-container {
  position: relative;
  width: 36px;
  height: 36px;
}

.totp-circle {
  width: 36px;
  height: 36px;
  transform: rotate(-90deg);
}

.totp-circle-bg {
  fill: none;
  stroke: #e5e7eb;
  stroke-width: 3;
}

.totp-circle-fg {
  fill: none;
  stroke: var(--oxd-primary-one-color);
  stroke-width: 3;
  stroke-dasharray: 100;
  stroke-linecap: round;
  transition: stroke-dashoffset 1s linear;
}

.totp-seconds {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.65rem;
  font-weight: 700;
  color: #374151;
}

/* History */
.history-box {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px 16px;
}

.history-item {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 4px;

  &:last-child {
    margin-bottom: 0;
  }
}

/* Footer */
.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fafb;
}

.footer-btn {
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 8px 16px;
  transition: all 0.2s;

  &.edit-btn {
    background: var(--oxd-primary-one-color);
    color: white;
    padding: 8px 24px;

    &:hover {
      background: var(--oxd-primary-one-darken-5-color);
    }
  }

  &.share-btn {
    background: #f0f9ff;
    color: #0369a1;
    border: 1.5px solid #bae6fd;
    padding: 8px 18px;
    display: flex;
    align-items: center;
    gap: 6px;

    &:hover {
      background: #e0f2fe;
      border-color: #7dd3fc;
    }
  }

  &.delete-btn {
    background: transparent;
    color: #dc2626; /* Red danger */
    font-size: 1.1rem;
    padding: 8px;

    &:hover {
      background: #fee2e2;
    }
  }
}
</style>
