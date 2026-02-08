<template>
  <div class="pm-container">
    <div class="pm-sidebar">
      <div class="pm-sidebar-header">
        <h3>Vault</h3>
        <button
          class="oxd-button oxd-button--medium oxd-button--main"
          @click="showAddItemModal = true"
        >
          <i class="oxd-icon bi-plus"></i> New Item
        </button>
      </div>
      <ul class="pm-nav">
        <li
          :class="{active: currentFilter === 'all'}"
          @click="currentFilter = 'all'"
        >
          <i class="oxd-icon bi-shield-lock"></i> All Items
        </li>
        <li
          :class="{active: currentFilter === 'favorites'}"
          @click="currentFilter = 'favorites'"
        >
          <i class="oxd-icon bi-star"></i> Favorites
        </li>
        <div class="pm-nav-divider"></div>
        <li class="pm-nav-header">Categories</li>
        <li
          v-for="category in categories"
          :key="category.id"
          @click="currentFilter = category.id"
        >
          <i class="oxd-icon bi-folder"></i> {{ category.name }}
        </li>
      </ul>
    </div>

    <div class="pm-main">
      <div class="pm-toolbar">
        <div class="pm-search">
          <input
            v-model="searchQuery"
            class="oxd-input oxd-input--active"
            placeholder="Search vault..."
          />
        </div>
      </div>

      <div class="pm-item-list">
        <div v-if="items.length > 0" class="oxd-table-card">
          <div
            v-for="item in items"
            :key="item.id"
            class="pm-item-row"
            @click="editItem(item)"
          >
            <div class="pm-item-icon">
              <i :class="getItemIcon(item.itemType)"></i>
            </div>
            <div class="pm-item-details">
              <strong>{{ item.name }}</strong>
              <span class="pm-item-subtitle">{{
                item.usernameEncrypted || 'No username'
              }}</span>
            </div>
            <div class="pm-item-actions">
              <button
                class="oxd-icon-button"
                @click.stop="openShareModal(item)"
              >
                <i class="oxd-icon bi-share"></i>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="pm-empty-state">
          <p>No items found.</p>
        </div>
      </div>
    </div>

    <vault-item-form
      v-if="showAddItemModal"
      :is-open="showAddItemModal"
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
import {defineComponent, ref, onMounted, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import VaultItemForm from './VaultItemForm.vue';
import ShareModal from './ShareModal.vue';

declare const window: any;

export default defineComponent({
  name: 'PasswordManagerLayout',
  components: {VaultItemForm, ShareModal},
  setup() {
    const items = ref<any[]>([]);
    const categories = ref<any[]>([]);
    const currentFilter = ref<string | number>('all');
    const searchQuery = ref('');
    const showAddItemModal = ref(false);
    const showShareModal = ref(false);
    const selectedItem = ref<any>(null);

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
      } catch (e) {
        console.error('Failed to fetch items', e);
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
      try {
        if (itemData.id) {
          await itemService.update(itemData.id, itemData);
        } else {
          await itemService.create(itemData);
        }
        closeModal();
        await fetchItems();
      } catch (e) {
        console.error('Save failed', e);
      }
    };

    const editItem = (item: any) => {
      selectedItem.value = item;
      showAddItemModal.value = true;
    };

    const closeModal = () => {
      selectedItem.value = null;
      showAddItemModal.value = false;
    };

    const openShareModal = (item: any) => {
      selectedItem.value = item;
      showShareModal.value = true;
    };

    const closeShareModal = () => {
      showShareModal.value = false;
      // Don't nullify selectedItem immediately if it's used elsewhere, but for now it's fine
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
      await Promise.all([fetchItems(), fetchCategories()]);
    });

    return {
      items: filteredItems,
      categories,
      currentFilter,
      searchQuery,
      showAddItemModal,
      selectedItem,
      handleSaveItem,
      editItem,
      closeModal,
      showShareModal,
      openShareModal,
      closeShareModal,
      getItemIcon,
    };
  },
});
</script>

<style lang="scss" scoped>
.pm-container {
  display: flex;
  height: calc(100vh - 60px); // Adjust based on layout
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.pm-sidebar {
  width: 260px;
  background: #f8f9fa;
  border-right: 1px solid #e9ecef;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.pm-sidebar-header {
  margin-bottom: 20px;
  h3 {
    margin-bottom: 15px;
    font-size: 1.2rem;
    color: #333;
  }
}

.pm-nav {
  list-style: none;
  padding: 0;
  margin: 0;

  li {
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 6px;
    color: #555;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: background 0.2s;

    &:hover {
      background: #e9ecef;
    }

    &.active {
      background: #e3f2fd;
      color: #0d6efd;
    }

    i {
      font-size: 1.1rem;
    }
  }
}

.pm-nav-header {
  font-size: 0.85rem;
  text-transform: uppercase;
  color: #888;
  font-weight: 600;
  margin-top: 20px;
  margin-bottom: 10px;
  padding-left: 15px;
  cursor: default !important;
  &:hover {
    background: none !important;
  }
}

.pm-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 20px;
}

.pm-toolbar {
  margin-bottom: 20px;
}

.pm-item-row {
  padding: 15px;
  border-bottom: 1px solid #eee;
  &:last-child {
    border-bottom: none;
  }
}
</style>
