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

        <div class="pm-nav-divider"></div>
        <div class="pm-nav-header">Categories</div>
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
      </div>

      <div class="pm-content">
        <div v-if="items.length > 0" class="pm-grid">
          <div
            v-for="item in items"
            :key="item.id"
            class="pm-card"
            @click="editItem(item)"
          >
            <div class="pm-card-icon" :class="item.itemType">
              <i :class="getItemIcon(item.itemType)"></i>
            </div>
            <div class="pm-card-info">
              <div class="pm-card-name">{{ item.name }}</div>
              <div class="pm-card-sub">
                {{ item.usernameEncrypted || 'Secure Entry' }}
              </div>
            </div>
            <div class="pm-card-actions">
              <button class="pm-icon-btn" @click.stop="openShareModal(item)">
                <i class="bi bi-share"></i>
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
      v-if="showShareModal"
      :is-open="showShareModal"
      :item-id="selectedItem?.id"
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
import {SecurityService} from '../services/SecurityService';

declare const window: any;

export default defineComponent({
  name: 'PasswordManagerLayout',
  components: {
    VaultItemForm,
    ShareModal,
    VaultUnlockModal,
    VaultItemView,
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

    const fetchItems = async () => {
      try {
        const response = await itemService.getAll();
        items.value = response.data.data;
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
      showUnlockModal.value = !SecurityService.isUnlocked();
    };

    const onUnlocked = () => {
      showUnlockModal.value = false;
      // Fetch items only after unlock if needed, but we fetch public metadata (encrypted blobs) at start/mounted
      // Decryption happens on item view/edit
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
        await itemService.delete(item.id);
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

    const filteredItems = computed(() => {
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

    onMounted(async () => {
      // Check unlock status immediately
      checkUnlockStatus();
      await Promise.all([fetchItems(), fetchCategories()]);
    });

    return {
      items: filteredItems,
      categories,
      currentFilter,
      searchQuery,

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

      // Utils
      getItemIcon,
      getCategoryName,
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
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  border: 1px solid #f0f0f0;
  transition: all 0.3s;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    border-color: #ff5500;
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
}

.pm-card-info {
  flex: 1;
}

.pm-card-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 4px;
}

.pm-card-sub {
  font-size: 0.85rem;
  color: #888;
}

.pm-icon-btn {
  background: none;
  border: none;
  color: #ccc;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.2s;

  &:hover {
    background: #f0f0f0;
    color: #ff5500;
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
