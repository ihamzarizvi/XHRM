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
import {APIService} from '@/core/util/services/api.service';

declare const window: any;

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
        // This derives the key in memory.
        await SecurityService.unlockVault(password.value);

        // --- Check/Generate User Keys for Sharing ---
        try {
          const userKeyService = new APIService(
            window.appGlobal.baseUrl,
            '/api/v2/password-manager/user-keys',
          );

          // Fetch existing keys
          const response = await userKeyService.getAll({userId: 'me'});
          const keys = response.data.data;

          if (keys && keys.length > 0) {
            // Existing User: Decrypt private key
            const myKey = keys[0];
            const privKeyStr = await SecurityService.decrypt(
              myKey.encryptedPrivateKey,
            );

            if (privKeyStr !== '[Encrypted Data]') {
              const privKey = await SecurityService.importKey(
                privKeyStr,
                'private',
              );
              SecurityService.setPrivateKey(privKey);
            } else {
              console.error('Failed to decrypt private key. Sharing disabled.');
            }
          } else {
            // New User: Generate keys
            console.log('Generating new RSA Key Pair for sharing...');
            const keyPair = await SecurityService.generateKeyPair();
            const pubKey = keyPair.publicKey as CryptoKey;
            const privKey = keyPair.privateKey as CryptoKey;
            const pubPem = await SecurityService.exportKey(pubKey);
            const privPem = await SecurityService.exportKey(privKey);
            const encPriv = await SecurityService.encrypt(privPem);

            await userKeyService.create({
              publicKey: pubPem,
              encryptedPrivateKey: encPriv,
            });
            SecurityService.setPrivateKey(privKey);
          }
        } catch (keyErr) {
          console.error('PKI Setup failed', keyErr);
          // Allow unlock to proceed even if sharing setup fails
        }

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
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-content {
  background: #ffffff;
  padding: 40px;
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.lock-icon {
  width: 64px;
  height: 64px;
  background: #fff7ed;
  color: var(--oxd-primary-one-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 20px;
}

h2 {
  color: #1f2937;
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
  padding: 10px 16px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
  color: #374151;

  &:focus {
    border-color: var(--oxd-primary-one-color);
    box-shadow: 0 0 0 3px var(--oxd-primary-one-alpha-10-color);
  }
}

.unlock-btn {
  background: var(--oxd-primary-one-color);
  color: white;
  border: none;
  padding: 0 24px;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;

  &:hover:not(:disabled) {
    background: var(--oxd-primary-one-darken-5-color);
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
