<template>
  <div class="pm-container">
    <!-- Sidebar -->
    <div class="pm-sidebar">
      <div class="pm-sidebar-header">
        <h3 class="pm-title">Vault</h3>
        <button class="pm-add-btn" @click="showAddItemModal = true">
          <i class="oxd-icon bi-plus-lg"></i> New Item
        </button>
      </div>

      <nav class="pm-nav">
        <div
          class="pm-nav-item"
          :class="{active: currentFilter === 'all'}"
          @click="currentFilter = 'all'"
        >
          <i class="oxd-icon bi-shield-lock"></i>
          <span>All Items</span>
        </div>
        <div
          class="pm-nav-item"
          :class="{active: currentFilter === 'favorites'}"
          @click="currentFilter = 'favorites'"
        >
          <i class="oxd-icon bi-star"></i>
          <span>Favorites</span>
        </div>
        <div
          class="pm-nav-item"
          :class="{active: currentFilter === 'shared'}"
          @click="
            currentFilter = 'shared';
            fetchSharedWithMe();
          "
        >
          <i class="oxd-icon bi-people"></i>
          <span>Shared with Me</span>
        </div>

        <div
          class="pm-nav-item"
          :class="{active: currentFilter === 'security'}"
          @click="currentFilter = 'security'"
        >
          <i class="oxd-icon bi-activity"></i>
          <span>Security Audit</span>
        </div>

        <div class="pm-nav-divider"></div>

        <div
          class="pm-nav-item"
          :class="{active: currentFilter === 'admin'}"
          @click="currentFilter = 'admin'"
        >
          <i class="oxd-icon bi-speedometer2"></i>
          <span>Admin Console</span>
        </div>

        <div class="pm-nav-divider"></div>
        <div
          class="pm-nav-header"
          style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
          "
          @click="showAddCategoryInput = !showAddCategoryInput"
        >
          Categories
          <i class="bi bi-plus" style="font-size: 1.2rem"></i>
        </div>

        <div
          v-if="showAddCategoryInput"
          class="pm-nav-item"
          style="padding: 5px 15px"
        >
          <input
            ref="categoryInput"
            v-model="newCategoryName"
            placeholder="New Category..."
            class="pm-search-input"
            style="padding: 8px; font-size: 0.9rem"
            @keyup.enter="createCategory"
            @blur="showAddCategoryInput = false"
          />
        </div>

        <div
          v-for="category in categories"
          :key="category.id"
          class="pm-nav-item"
          :class="{active: currentFilter === category.id}"
          @click="currentFilter = category.id"
        >
          <i class="oxd-icon bi-folder"></i>
          <span>{{ category.name }}</span>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="pm-main">
      <div class="pm-toolbar">
        <div class="pm-search-container">
          <i class="bi bi-search pm-search-icon"></i>
          <input
            v-model="searchQuery"
            class="pm-search-input"
            placeholder="Search your secure vault..."
          />
        </div>
        <button class="pm-lock-btn" title="Lock Vault" @click="lockVault">
          <i class="bi bi-lock-fill"></i>
        </button>
      </div>

      <div v-if="currentFilter === 'security'" class="pm-content">
        <security-dashboard :items="allDataForAudit" @edit="editItem" />
      </div>

      <div v-else-if="currentFilter === 'admin'" class="pm-content">
        <admin-dashboard />
      </div>

      <div v-else class="pm-content">
        <div v-if="items.length > 0" class="pm-grid">
          <div
            v-for="item in items"
            :key="item.id"
            class="pm-card"
            @click="viewItem(item)"
          >
            <!-- Card Content (Same as before) -->
            <button
              class="pm-favorite-btn"
              :class="{active: item.favorite}"
              @click.stop="toggleFavorite(item)"
            >
              <i :class="item.favorite ? 'bi-star-fill' : 'bi-star'"></i>
            </button>
            <div class="pm-card-icon" :class="item.itemType">
              <img
                v-if="getFaviconUrl(item)"
                :src="getFaviconUrl(item)"
                class="pm-favicon"
              />
              <i v-else :class="getItemIcon(item.itemType)"></i>
            </div>
            <div class="pm-card-info">
              <div class="pm-card-name">{{ item.name }}</div>
              <div class="pm-card-sub">
                {{ item.username || 'Secure Entry' }}
              </div>
            </div>
            <div class="pm-card-actions">
              <button
                class="pm-icon-btn"
                title="Copy Username"
                @click.stop="copyToClipboard(item.username)"
              >
                <i class="bi bi-person"></i>
              </button>
              <button
                class="pm-icon-btn"
                title="Copy Password"
                @click.stop="copyToClipboard(item.password)"
              >
                <i class="bi bi-key"></i>
              </button>
              <button
                v-if="item.url"
                class="pm-icon-btn"
                title="Launch"
                @click.stop="launchUrl(item.url)"
              >
                <i class="bi bi-box-arrow-up-right"></i>
              </button>
              <button
                class="pm-icon-btn"
                title="Edit"
                @click.stop="editItem(item)"
              >
                <i class="bi bi-pencil"></i>
              </button>
              <button
                class="pm-icon-btn delete"
                title="Delete"
                @click.stop="deleteItem(item)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="pm-empty">
          <div class="pm-empty-icon">
            <i class="bi bi-safe2"></i>
          </div>
          <h3>Your vault is empty</h3>
          <p>Store your first password securely.</p>
        </div>
      </div>
    </div>

    <!-- Global Modals -->
    <!-- Global Modals -->
    <vault-unlock-modal v-if="showUnlockModal" @unlocked="onUnlocked" />

    <vault-item-view
      v-if="showViewModal && selectedItem"
      :is-open="showViewModal"
      :item="selectedItem"
      :category-name="getCategoryName(selectedItem.categoryId)"
      @close="closeViewModal"
      @edit="editItem"
      @delete="deleteItem"
    />

    <vault-item-form
      v-if="showAddItemModal"
      :is-open="showAddItemModal"
      :is-loading="isSavingItem"
      :item="selectedItem"
      @close="closeModal"
      @save="handleSaveItem"
    />

    <share-modal
      v-if="showShareModal && selectedItem"
      :is-open="showShareModal"
      :item="selectedItem"
      @close="closeShareModal"
    />
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, onMounted, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import VaultItemForm from './VaultItemForm.vue';
import ShareModal from './ShareModal.vue';
import VaultUnlockModal from './VaultUnlockModal.vue';
import VaultItemView from './VaultItemView.vue';
import SecurityDashboard from './SecurityDashboard.vue';
import AdminDashboard from './AdminDashboard.vue';
import {SecurityService} from '../services/SecurityService';

declare const window: any;

export default defineComponent({
  name: 'PasswordManagerLayout',
  components: {
    VaultItemForm,
    ShareModal,
    VaultUnlockModal,
    VaultItemView,
    SecurityDashboard,
    AdminDashboard,
  },
  setup() {
    const items = ref<any[]>([]);
    const categories = ref<any[]>([]);
    const currentFilter = ref<string | number>('all');
    const searchQuery = ref('');

    // Modal states
    const showAddItemModal = ref(false);
    const showShareModal = ref(false);
    const showUnlockModal = ref(true); // Default to locked
    const showViewModal = ref(false);

    const selectedItem = ref<any>(null);
    const isSavingItem = ref(false);

    // Initialize Services
    const itemService = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/items',
    );
    const categoryService = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/categories',
    );
    const shareService = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/shares',
    );

    const sharedItems = ref<any[]>([]);

    const fetchSharedWithMe = async () => {
      if (!SecurityService.isVaultUnlocked()) return;
      try {
        const response = await shareService.getAll();
        const rawShares = response.data.data;

        const privKey = SecurityService.getPrivateKey();
        if (!privKey) {
          console.warn(
            'No private key available. Shared items cannot be decrypted.',
          );
          sharedItems.value = rawShares.map((s: any) => ({
            ...s,
            _isShared: true,
            username: '[Keys not loaded]',
            password: '',
            url: '',
            notes: '',
          }));
          return;
        }

        sharedItems.value = await Promise.all(
          rawShares.map(async (share: any) => {
            try {
              // Decrypt the item key using your RSA private key
              const itemKeyRaw = await SecurityService.decryptRSA(
                share.encryptedKeyForRecipient,
                privKey,
              );
              const itemKey = await SecurityService.importAESKey(itemKeyRaw);

              // Now fetch the actual item data
              const itemResponse = await itemService.get(share.vaultItemId);
              const item = itemResponse.data.data;

              return {
                ...item,
                _isShared: true,
                _sharedBy: share.sharedByUserId,
                _sharePermission: share.permission,
                _decryptedItemKey: itemKey,
                username: item.usernameEncrypted
                  ? await SecurityService.decrypt(
                      item.usernameEncrypted,
                      itemKey,
                    )
                  : '',
                password: item.passwordEncrypted
                  ? await SecurityService.decrypt(
                      item.passwordEncrypted,
                      itemKey,
                    )
                  : '',
                url: item.urlEncrypted
                  ? await SecurityService.decrypt(item.urlEncrypted, itemKey)
                  : '',
                notes: item.notesEncrypted
                  ? await SecurityService.decrypt(item.notesEncrypted, itemKey)
                  : '',
                totpSecret: item.totpSecretEncrypted
                  ? await SecurityService.decrypt(
                      item.totpSecretEncrypted,
                      itemKey,
                    )
                  : '',
              };
            } catch (e) {
              console.error(
                'Failed to decrypt shared item',
                share.vaultItemId,
                e,
              );
              return {
                ...share,
                _isShared: true,
                name: `Shared Item #${share.vaultItemId}`,
                itemType: 'login',
                username: '[Decryption Failed]',
              };
            }
          }),
        );
      } catch (e: any) {
        console.error('Failed to fetch shared items', e);
      }
    };

    const fetchItems = async () => {
      try {
        const response = await itemService.getAll();
        const rawItems = response.data.data;

        if (SecurityService.isVaultUnlocked()) {
          items.value = await Promise.all(
            rawItems.map(async (item: any) => {
              try {
                let itemKey: CryptoKey | undefined = undefined;

                if (item.encryptedItemKey) {
                  try {
                    const itemKeyStr = await SecurityService.decrypt(
                      item.encryptedItemKey,
                    );
                    if (itemKeyStr && itemKeyStr !== '[Encrypted Data]') {
                      itemKey = await SecurityService.importAESKey(itemKeyStr);
                    }
                  } catch (e) {
                    console.error('Failed to decrypt item key', e);
                  }
                }

                // Decrypt fields using Item Key (or Master Key if undefined)
                return {
                  ...item,
                  _decryptedItemKey: itemKey, // Store for reuse (Edit/Share)

                  username: item.usernameEncrypted
                    ? await SecurityService.decrypt(
                        item.usernameEncrypted,
                        itemKey,
                      )
                    : '',
                  password: item.passwordEncrypted
                    ? await SecurityService.decrypt(
                        item.passwordEncrypted,
                        itemKey,
                      )
                    : '',
                  url: item.urlEncrypted
                    ? await SecurityService.decrypt(item.urlEncrypted, itemKey)
                    : '',
                  notes: item.notesEncrypted
                    ? await SecurityService.decrypt(
                        item.notesEncrypted,
                        itemKey,
                      )
                    : '',
                  totpSecret: item.totpSecretEncrypted
                    ? await SecurityService.decrypt(
                        item.totpSecretEncrypted,
                        itemKey,
                      )
                    : '',
                };
              } catch (e) {
                console.error(`Failed to decrypt item ${item.id}`, e);
                return item; // Keep encrypted if failure
              }
            }),
          );
        } else {
          items.value = rawItems;
        }
      } catch (e: any) {
        console.error('Failed to fetch items', e);
        if (e.response?.data?.error) {
          console.error('Fetch error details:', e.response.data.error);
        }
      }
    };

    const fetchCategories = async () => {
      try {
        const response = await categoryService.getAll();
        categories.value = response.data.data;
      } catch (e) {
        console.error('Failed to fetch categories', e);
      }
    };

    const handleSaveItem = async (itemData: any) => {
      isSavingItem.value = true;
      try {
        if (itemData.id) {
          await itemService.update(itemData.id, itemData);
        } else {
          await itemService.create(itemData);
        }
        closeModal();
        await fetchItems();
      } catch (e: any) {
        console.error('Save failed', e);
        // ... (Error handling code remains the same)
        alert('Failed to save item. See console for details.');
      } finally {
        isSavingItem.value = false;
      }
    };

    // --- Unlock Flow ---
    const checkUnlockStatus = () => {
      const unlocked = SecurityService.isVaultUnlocked();
      showUnlockModal.value = !unlocked;
      if (unlocked) {
        fetchItems();
        fetchCategories();
      }
    };

    const onUnlocked = () => {
      showUnlockModal.value = false;
      fetchItems();
      fetchCategories();
    };

    const lockVault = () => {
      SecurityService.lockVault();
      items.value = []; // Clear data from memory
      showUnlockModal.value = true;
    };

    // --- Item Interactions ---
    const viewItem = (item: any) => {
      selectedItem.value = item;
      showViewModal.value = true;
    };

    const editItem = (item?: any) => {
      selectedItem.value = item || null; // null means new item
      showAddItemModal.value = true;
      showViewModal.value = false; // Close view modal if coming from there
    };

    const closeModal = () => {
      selectedItem.value = null;
      showAddItemModal.value = false;
    };

    const closeViewModal = () => {
      selectedItem.value = null;
      showViewModal.value = false;
    };

    const openShareModal = (item: any) => {
      selectedItem.value = item;
      showShareModal.value = true;
    };

    const closeShareModal = () => {
      showShareModal.value = false;
    };

    const deleteItem = async (item: any) => {
      try {
        // XHRM uses bulk delete pattern: DELETE /items with {ids: []} body
        await itemService.deleteAll({ids: [item.id]});
        closeViewModal();
        await fetchItems();
      } catch (e) {
        console.error('Failed to delete item', e);
        alert('Failed to delete item.');
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

    const getCategoryName = (id: number) => {
      const cat = categories.value.find((c) => c.id === id);
      return cat ? cat.name : '';
    };

    // Helper functions
    const normalizeUrl = (url: string) => {
      if (!url) return '';
      if (url.match(/^https?:\/\//i)) return url;
      return 'https://' + url;
    };

    const getFaviconUrl = (item: any) => {
      if (!item.url) return undefined;
      try {
        const domain = new URL(normalizeUrl(item.url)).hostname;
        return `https://www.google.com/s2/favicons?domain=${domain}&sz=64`;
      } catch (e) {
        return undefined;
      }
    };

    const copyToClipboard = async (text: string) => {
      if (!text) return;
      try {
        await navigator.clipboard.writeText(text);
      } catch (err) {
        console.error('Failed to copy', err);
      }
    };

    const launchUrl = (url: string) => {
      if (!url) return;
      window.open(normalizeUrl(url), '_blank');
    };

    const toggleFavorite = async (item: any) => {
      const originalState = item.favorite;
      item.favorite = !originalState; // Optimistic update

      try {
        await itemService.update(item.id, item);
      } catch (e) {
        console.error('Failed to toggle favorite', e);
        item.favorite = originalState; // Revert on failure
        alert('Failed to update favorite status.');
      }
    };

    // --- Computed ---
    const filteredItems = computed(() => {
      // If viewing shared items, return those instead
      if (currentFilter.value === 'shared') {
        let result = sharedItems.value;
        if (searchQuery.value) {
          const query = searchQuery.value.toLowerCase();
          result = result.filter((i) => i.name?.toLowerCase().includes(query));
        }
        return result;
      }

      let result = items.value;

      if (currentFilter.value === 'favorites') {
        result = result.filter((i) => i.favorite);
      } else if (typeof currentFilter.value === 'number') {
        result = result.filter((i) => i.categoryId === currentFilter.value);
      }

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((i) => i.name.toLowerCase().includes(query));
      }

      return result;
    });

    const allDataForAudit = computed(() => {
      return [...items.value, ...sharedItems.value];
    });

    onMounted(async () => {
      checkUnlockStatus();
      await Promise.all([fetchItems(), fetchCategories()]);
    });

    const showAddCategoryInput = ref(false);
    const newCategoryName = ref('');

    const createCategory = async () => {
      if (!newCategoryName.value.trim()) return;
      try {
        await categoryService.create({name: newCategoryName.value});
        newCategoryName.value = '';
        showAddCategoryInput.value = false;
        await fetchCategories();
      } catch (e) {
        console.error('Failed to create category', e);
        alert('Failed to create category');
      }
    };

    return {
      items: filteredItems,
      categories,
      currentFilter,
      searchQuery,

      // Category Creation
      showAddCategoryInput,
      newCategoryName,
      createCategory,
      allDataForAudit,

      // Modals
      showAddItemModal,
      showShareModal,
      showUnlockModal,
      showViewModal,
      selectedItem,
      isSavingItem,

      // Actions
      handleSaveItem,
      editItem,
      viewItem,
      closeModal,
      closeViewModal,
      openShareModal,
      closeShareModal,
      deleteItem,
      onUnlocked,
      lockVault,

      // New Actions
      toggleFavorite,
      copyToClipboard,
      launchUrl,
      normalizeUrl,

      // Utils
      getItemIcon,
      getFaviconUrl,
      getCategoryName,

      // Sharing
      fetchSharedWithMe,
    };
  },
});
</script>

<style lang="scss" scoped>
.pm-container {
  display: flex;
  height: calc(100vh - 100px);
  background: #fdfdfd;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  margin: 10px;
}

/* Sidebar Styling */
.pm-sidebar {
  width: 280px;
  background: #ffffff;
  border-right: 1px solid #f0f0f0;
  padding: 30px 20px;
  display: flex;
  flex-direction: column;
}

.pm-sidebar-header {
  margin-bottom: 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.pm-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0;
}

.pm-add-btn {
  background: linear-gradient(135deg, #ff7b00 0%, #ff5500 100%);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 15px rgba(255, 85, 0, 0.3);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 85, 0, 0.4);
  }

  &:active {
    transform: translateY(0);
  }
}

.pm-nav {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.pm-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 15px;
  border-radius: 10px;
  color: #666;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;

  i {
    font-size: 1.25rem;
  }

  &:hover {
    background: #f8f9fa;
    color: #000;
  }

  &.active {
    background: #fff0e6;
    color: #ff5500;
  }
}

.pm-nav-divider {
  height: 1px;
  background: #f0f0f0;
  margin: 20px 0;
}

.pm-nav-header {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #aaa;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 10px;
  padding-left: 15px;
}

/* Main Content Styling */
.pm-main {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.pm-toolbar {
  padding: 20px 40px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pm-lock-btn {
  background: none;
  border: 1px solid #e0e0e0;
  color: #666;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  transition: all 0.2s;
  margin-left: 16px;

  &:hover {
    background: #ffeee6;
    border-color: #ff5500;
    color: #ff5500;
  }
}

.pm-search-container {
  position: relative;
  max-width: 600px;
}

.pm-search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
  font-size: 1.2rem;
}

.pm-search-input {
  width: 100%;
  padding: 14px 15px 14px 45px;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s;
  background: #f9f9f9;

  &:focus {
    outline: none;
    border-color: #ff5500;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(255, 85, 0, 0.1);
  }
}

.pm-content {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
  background: #fafafa;
}

.pm-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.pm-card {
  position: relative;
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  border: 1px solid #f0f0f0;
  transition: all 0.3s;
  overflow: hidden;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    border-color: #ff5500;

    .pm-card-actions {
      opacity: 1;
      transform: translateY(0);
    }
  }
}

.pm-favorite-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  color: #e0e0e0;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s;
  z-index: 2;

  &:hover {
    color: #ffd700;
    transform: scale(1.1);
  }

  &.active {
    color: #ffd700;
  }
}

.pm-card-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  overflow: hidden;
  position: relative;
  background: #f5f5f5;

  &.login {
    background: #e3f2fd;
    color: #1976d2;
  }
  &.card {
    background: #f1f8e9;
    color: #388e3c;
  }
  &.note {
    background: #fff3e0;
    color: #f57c00;
  }

  .pm-favicon {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.pm-card-info {
  flex: 1;
  min-width: 0;
}

.pm-card-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-card-sub {
  font-size: 0.85rem;
  color: #888;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pm-card-actions {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.95);
  padding: 10px;
  display: flex;
  justify-content: space-around;
  border-top: 1px solid #f0f0f0;
  opacity: 0;
  transform: translateY(100%);
  transition: all 0.3s ease-in-out;
}

.pm-icon-btn {
  background: none;
  border: none;
  color: #666;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;

  &:hover {
    background: #f0f0f0;
    color: #ff5500;
  }

  &.delete:hover {
    background: #ffebee;
    color: #d32f2f;
  }
}

.pm-empty {
  text-align: center;
  margin-top: 100px;
  color: #aaa;

  .pm-empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
  }

  h3 {
    margin-bottom: 10px;
    color: #666;
  }
}
</style>
