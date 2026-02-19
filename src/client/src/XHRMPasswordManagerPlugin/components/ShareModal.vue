<template>
  <oxd-dialog :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="oxd-text oxd-text--h3">Share "{{ item.name }}"</h3>
    </template>

    <div class="share-body">
      <!-- Search User -->
      <div class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">Search User</label>
          <div class="search-row">
            <input
              v-model="searchQuery"
              class="oxd-input oxd-input--active"
              placeholder="Enter username or employee name..."
              @keyup.enter="searchUser"
            />
            <button
              class="oxd-button oxd-button--medium oxd-button--secondary"
              :disabled="isSearching || !searchQuery"
              @click="searchUser"
            >
              <span v-if="isSearching">...</span>
              <span v-else>Search</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Search Error -->
      <div v-if="searchError" class="share-error">{{ searchError }}</div>

      <!-- Found User -->
      <div v-if="foundUser" class="found-user">
        <i class="bi bi-person-check-fill"></i>
        <div class="found-user-info">
          <strong>{{ foundUser.employeeName || foundUser.userName }}</strong>
          <span class="found-user-sub">User ID: {{ foundUser.id }}</span>
        </div>
        <i
          class="bi bi-key-fill key-ready"
          title="User has sharing keys set up"
        ></i>
      </div>

      <!-- Permission -->
      <div v-if="foundUser" class="oxd-form-row">
        <div class="oxd-input-group oxd-input-field-bottom-space">
          <label class="oxd-label">Permission</label>
          <select v-model="permission" class="oxd-select-wrapper">
            <option value="read">Read Only</option>
            <option value="write">Read/Write</option>
          </select>
        </div>
      </div>

      <!-- No Item Key Warning -->
      <div v-if="foundUser && !item._decryptedItemKey" class="share-warning">
        <i class="bi bi-exclamation-triangle-fill"></i>
        This item uses legacy encryption. Please edit and re-save it first to
        enable sharing.
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
        :disabled="!foundUser || !item._decryptedItemKey || isSharing"
        @click="share"
      >
        <span v-if="isSharing">Sharing...</span>
        <span v-else>Share</span>
      </button>
    </template>
  </oxd-dialog>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import {SecurityService} from '../services/SecurityService';

declare const window: any;

export default defineComponent({
  name: 'ShareModal',
  props: {
    isOpen: {type: Boolean, required: true},
    item: {type: Object, required: true},
  },
  emits: ['close'],
  setup(props, {emit}) {
    const searchQuery = ref('');
    const permission = ref('read');
    const isSearching = ref(false);
    const foundUser = ref<any>(null);
    const searchError = ref('');
    const isSharing = ref(false);

    const userKeyService = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/user-keys',
    );
    const shareService = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/shares',
    );

    const searchUser = async () => {
      if (!searchQuery.value) return;
      isSearching.value = true;
      searchError.value = '';
      foundUser.value = null;

      try {
        // Search admin users API directly (requires status param)
        const userService = new APIService(
          window.appGlobal.baseUrl,
          '/api/v2/admin/users',
        );
        const userResp = await userService.getAll({
          limit: 10,
          username: searchQuery.value,
          status: true,
        });

        let users = userResp.data.data;
        if (!users || users.length === 0) {
          // Try searching by employee name via PIM
          const empService = new APIService(
            window.appGlobal.baseUrl,
            '/api/v2/pim/employees',
          );
          const empResp = await empService.getAll({
            limit: 5,
            nameOrId: searchQuery.value,
          });

          const employees = empResp.data.data;
          if (!employees || employees.length === 0) {
            searchError.value = 'No user found with that name or username.';
            return;
          }

          // Find user account for the first matching employee
          const emp = employees[0];
          const empId = emp.empNumber || emp.id;
          const userByEmp = await userService.getAll({
            limit: 5,
            empNumber: empId,
            status: true,
          });
          users = userByEmp.data.data;
          if (!users || users.length === 0) {
            searchError.value = `Employee "${emp.firstName} ${emp.lastName}" has no system user account.`;
            return;
          }
        }

        const user = users[0];
        const empName =
          user.employee?.firstName && user.employee?.lastName
            ? `${user.employee.firstName} ${user.employee.lastName}`
            : user.userName;

        // Check if this user has PKI keys
        const keyResponse = await userKeyService.getAll({
          userId: String(user.id),
        });
        const keys = keyResponse.data.data;

        if (keys && keys.length > 0 && keys[0].publicKey) {
          // publicKey is JSON {salt, rsaPublicKey} — extract the RSA key
          let rsaPubKey = keys[0].publicKey;
          try {
            const parsed = JSON.parse(keys[0].publicKey);
            if (parsed.rsaPublicKey) {
              rsaPubKey = parsed.rsaPublicKey;
            } else {
              searchError.value = `User "${empName}" hasn't set up sharing keys yet.`;
              return;
            }
          } catch {
            searchError.value = `User "${empName}" hasn't set up sharing keys yet.`;
            return;
          }
          foundUser.value = {
            id: user.id,
            employeeName: empName,
            userName: user.userName,
            publicKey: rsaPubKey,
          };
        } else {
          searchError.value = `User "${empName}" hasn't set up their vault yet. They need to unlock their vault at least once.`;
        }
      } catch (e) {
        console.error('Search failed', e);
        searchError.value = 'Error searching for user.';
      } finally {
        isSearching.value = false;
      }
    };

    const share = async () => {
      if (!foundUser.value || !foundUser.value.publicKey) return;

      const itemKey = props.item._decryptedItemKey;
      if (!itemKey) {
        alert(
          'This item uses legacy encryption. Please edit and save it to upgrade before sharing.',
        );
        return;
      }

      isSharing.value = true;
      try {
        // 1. Import Recipient's Public Key
        const recipientPubKey = await SecurityService.importKey(
          foundUser.value.publicKey,
          'public',
        );

        // 2. Export the item's AES key as raw base64
        const itemKeyRaw = await SecurityService.exportAESKey(itemKey);

        // 3. Encrypt Item Key with Recipient's RSA Public Key
        const encryptedKeyForRecipient = await SecurityService.encryptRSA(
          itemKeyRaw,
          recipientPubKey,
        );

        // 4. Create the share record on the server
        await shareService.create({
          vaultItemId: props.item.id,
          sharedWithUserId: foundUser.value.id,
          permission: permission.value,
          encryptedKeyForRecipient: encryptedKeyForRecipient,
        });

        alert('Item shared successfully!');
        emit('close');
      } catch (e) {
        console.error('Share failed', e);
        alert('Failed to share item. See console for details.');
      } finally {
        isSharing.value = false;
      }
    };

    return {
      searchQuery,
      permission,
      share,
      searchUser,
      isSearching,
      foundUser,
      searchError,
      isSharing,
    };
  },
});
</script>

<style lang="scss" scoped>
.share-body {
  padding: 10px 0;
}

.search-row {
  display: flex;
  gap: 10px;
}

.search-row .oxd-input {
  flex: 1;
}

.share-error {
  color: #d32f2f;
  font-size: 0.85rem;
  margin-bottom: 12px;
  padding: 8px 12px;
  background: #ffebee;
  border-radius: 6px;
}

.share-warning {
  color: #e65100;
  font-size: 0.85rem;
  margin-top: 8px;
  padding: 10px 12px;
  background: #fff3e0;
  border-radius: 6px;

  i {
    margin-right: 6px;
  }
}

.found-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #e8f5e9;
  border-radius: 8px;
  margin-bottom: 16px;

  > i:first-child {
    font-size: 1.5rem;
    color: #2e7d32;
  }
}

.found-user-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.found-user-sub {
  font-size: 0.8rem;
  color: #666;
}

.key-ready {
  color: var(--oxd-primary-one-color);
  font-size: 1.2rem;
}
</style>
