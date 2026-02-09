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
          <div class="item-icon- large" :class="item.itemType">
            <i :class="getItemIcon(item.itemType)"></i>
          </div>
          <div class="item-header-info">
            <div class="item-name">{{ item.name }}</div>
            <div class="item-category">
              <i class="bi bi-folder"></i>
              {{ categoryName || 'No folder' }}
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
                <div class="totp-value">
                  <span class="code">{{ currentTotp || 'Generating...' }}</span>
                  <div
                    class="totp-timer"
                    :style="{width: timerWidth + '%'}"
                  ></div>
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
                    :href="decryptedData.url"
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
        <button class="footer-btn delete-btn" @click="confirmDelete">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, onMounted, onUnmounted, PropType} from 'vue';
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
  emits: ['close', 'edit', 'delete'],
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
        const [user, pass, url, notes, secret] = await Promise.all([
          props.item.usernameEncrypted
            ? SecurityService.decrypt(props.item.usernameEncrypted)
            : '',
          props.item.passwordEncrypted
            ? SecurityService.decrypt(props.item.passwordEncrypted)
            : '',
          props.item.urlEncrypted
            ? SecurityService.decrypt(props.item.urlEncrypted)
            : '',
          props.item.notesEncrypted
            ? SecurityService.decrypt(props.item.notesEncrypted)
            : '',
          props.item.totpSecretEncrypted
            ? SecurityService.decrypt(props.item.totpSecretEncrypted)
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
        if (decryptedData.value.totpSecret) {
          const secret = decryptedData.value.totpSecret;
          const code = TOTPService.generateCode(secret);
          if (code) currentTotp.value = code;

          const remaining = TOTPService.getRemainingSeconds();
          timerWidth.value = (remaining / 30) * 100;
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

    const formatDate = (dateString: string) => {
      if (!dateString) return 'Unknown';
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Unknown';
        return format(date, 'MMM d, yyyy, h:mm a');
      } catch (e) {
        return 'Unknown';
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

    return {
      decryptedData,
      showPassword,
      currentTotp,
      timerWidth,
      copyToClipboard,
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
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #0f172a; /* Dark theme base from reference */
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #e2e8f0;
  border: 1px solid #1e293b;
}

.modal-header {
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #1e293b;

  .modal-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    color: #f8fafc;
  }

  .modal-close {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    &:hover {
      background: #334155;
      color: #fff;
    }
  }
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  max-height: 80vh;
}

/* Header Card */
.item-header-card {
  background: #1e293b;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  border: 1px solid #334155;

  .item-icon- {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: #0f172a;
    color: #cbd5e1;
  }

  .item-header-info {
    .item-name {
      font-size: 1.1rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 4px;
    }
    .item-category {
      font-size: 0.85rem;
      color: #94a3b8;
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
    color: #cbd5e1;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
}

.fields-group {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  overflow: hidden;
}

.field-row {
  padding: 12px 16px;
  border-bottom: 1px solid #334155;
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
    color: #64748b;
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
    color: #f1f5f9;
    font-weight: 500;
    word-break: break-all;
    flex: 1;

    &.password {
      font-family: monospace;
      letter-spacing: 1px;
    }

    &.url {
      color: #38bdf8;
    }

    &.note-text {
      white-space: pre-wrap;
      font-size: 0.9rem;
      color: #cbd5e1;
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
    color: #94a3b8;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: all 0.2s;

    &:hover {
      background: #334155;
      color: #fff;
    }

    &.top-right {
      position: absolute;
      top: 12px;
      right: 12px;
    }
  }

  .setup-totp {
    font-size: 0.9rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
  }
}

/* TOTP Special Styling */
.totp-value {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 120px;

  .code {
    font-family: monospace;
    font-size: 1.1rem;
    font-weight: 600;
    color: #a5b4fc;
    letter-spacing: 2px;
  }

  .totp-timer {
    height: 3px;
    background: #6366f1;
    border-radius: 2px;
    transition: width 1s linear;
  }
}

/* History */
.history-box {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 12px 16px;
}

.history-item {
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 4px;

  &:last-child {
    margin-bottom: 0;
  }
}

/* Footer */
.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #1e293b;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0f172a;
}

.footer-btn {
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 8px 16px;
  transition: all 0.2s;

  &.edit-btn {
    background: #3b82f6; /* Blue action */
    color: white;
    padding: 8px 24px;

    &:hover {
      background: #2563eb;
    }
  }

  &.delete-btn {
    background: transparent;
    color: #ef4444; /* Red danger */
    font-size: 1.1rem;
    padding: 8px;

    &:hover {
      background: rgba(239, 68, 68, 0.1);
    }
  }
}
</style>
