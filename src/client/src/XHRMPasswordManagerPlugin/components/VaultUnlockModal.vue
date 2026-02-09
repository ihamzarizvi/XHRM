<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <div class="lock-icon">
        <i class="bi bi-shield-lock"></i>
      </div>
      <h2>Unlock Your Vault</h2>
      <p class="subtitle">Enter your XHRM password to decrypt your data.</p>

      <form @submit.prevent="unlock">
        <div class="input-group">
          <input
            v-model="password"
            type="password"
            placeholder="Enter password..."
            class="password-input"
            autofocus
            :disabled="isUnlocking"
          />
          <button
            type="submit"
            class="unlock-btn"
            :disabled="isUnlocking || !password"
          >
            <span v-if="isUnlocking" class="spinner"></span>
            <span v-else>Unlock</span>
          </button>
        </div>
        <div v-if="error" class="error-msg">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref} from 'vue';
import {SecurityService} from '../services/SecurityService';

export default defineComponent({
  name: 'VaultUnlockModal',
  emits: ['unlocked'],
  setup(props, {emit}) {
    const password = ref('');
    const isUnlocking = ref(false);
    const error = ref('');

    const unlock = async () => {
      if (!password.value) return;
      isUnlocking.value = true;
      error.value = '';

      try {
        // In a real app, the salt would come from the user's profile or DB.
        // For MVP, we use a deterministic app-wide salt or derivation based on username.
        // Ideally, we fetch the specific user's salt from an API first.
        const fakeSalt = 'XHRM_VAULT_SALT_V1';

        // This derives the key in memory.
        await SecurityService.unlockVault(password.value, fakeSalt);

        emit('unlocked');
      } catch (e: any) {
        console.error('Unlock failed', e);
        error.value = 'Failed to derive encryption key.';
      } finally {
        isUnlocking.value = false;
      }
    };

    return {password, isUnlocking, error, unlock};
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
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-content {
  background: #ffffff;
  padding: 40px;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.lock-icon {
  width: 64px;
  height: 64px;
  background: #eff6ff;
  color: #3b82f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 20px;
}

h2 {
  color: #1e293b;
  margin-bottom: 8px;
  font-size: 1.5rem;
  font-weight: 700;
}

.subtitle {
  color: #64748b;
  margin-bottom: 24px;
  font-size: 0.95rem;
}

.input-group {
  display: flex;
  gap: 10px;
}

.password-input {
  flex: 1;
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;

  &:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
}

.unlock-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;

  &:hover:not(:disabled) {
    background: #2563eb;
  }

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.error-msg {
  color: #ef4444;
  margin-top: 12px;
  font-size: 0.85rem;
}

.spinner {
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-left-color: white;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
