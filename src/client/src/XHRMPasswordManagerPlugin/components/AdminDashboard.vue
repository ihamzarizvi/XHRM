<template>
  <div class="pm-admin-dashboard">
    <div class="pm-header">
      <h2>Admin Console</h2>
      <p>Overview of organization's password security health.</p>
    </div>

    <div v-if="loading" class="pm-loading">
      <div class="spinner"></div>
      Loading system statistics...
    </div>

    <div v-else-if="error" class="pm-error-state">
      <i class="bi bi-shield-slash-fill"></i>
      <h3>Access Denied</h3>
      <p>You do not have permission to view the administrative console.</p>
    </div>

    <div v-else class="pm-stats-grid">
      <!-- Security Score -->
      <div class="pm-stat-card score">
        <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
        <div class="stat-value">{{ stats.securityScore }}%</div>
        <div class="stat-label">Overall Security Score</div>
      </div>

      <!-- Total Items -->
      <div class="pm-stat-card">
        <div class="stat-icon"><i class="bi bi-key"></i></div>
        <div class="stat-value">{{ stats.totalItems }}</div>
        <div class="stat-label">Total Vault Items</div>
      </div>

      <!-- Users -->
      <div class="pm-stat-card">
        <div class="stat-icon"><i class="bi bi-people"></i></div>
        <div class="stat-value">{{ stats.userCount }}</div>
        <div class="stat-label">Active Users</div>
      </div>

      <!-- Shared -->
      <div class="pm-stat-card">
        <div class="stat-icon"><i class="bi bi-share"></i></div>
        <div class="stat-value">{{ stats.shareCount }}</div>
        <div class="stat-label">Shared Items</div>
      </div>

      <!-- Breached -->
      <div class="pm-stat-card danger">
        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-value">{{ stats.breachedCount }}</div>
        <div class="stat-label">Compromised Credentials</div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, onMounted} from 'vue';
import {APIService} from '@/core/util/services/api.service';

declare const window: any;

export default defineComponent({
  name: 'AdminDashboard',
  setup() {
    const stats = ref<any>({});
    const loading = ref(true);
    const error = ref(false);

    // Endpoint: GET /api/v2/password-manager/admin/stats
    const api = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/password-manager/admin/stats',
    );

    const fetchStats = async () => {
      try {
        const response = await api.getAll();
        // Assuming API returns resource wrapper { data: { ...stats } }
        stats.value = response.data.data;
      } catch (e: any) {
        console.error('Failed to fetch admin stats', e);
        if (e.response && e.response.status === 403) {
          error.value = true;
        } else {
          // If generic failure, maybe also show error but log it
          // For now treat as loading error?
          // Let's show empty or error state
        }
      } finally {
        loading.value = false;
      }
    };

    onMounted(fetchStats);

    return {stats, loading, error};
  },
});
</script>

<style lang="scss" scoped>
.pm-admin-dashboard {
  padding: 30px;
  height: 100%;
  overflow-y: auto;
}

.pm-header {
  margin-bottom: 30px;
  h2 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.5rem;
  }
  p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
  }
}

.pm-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #666;
  gap: 10px;

  .spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-top-color: #ff5500;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.pm-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.pm-stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  border: 1px solid #f0f0f0;
  transition: transform 0.2s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    background: #e6f7ff;
    color: #1890ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 12px;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
  }

  .stat-label {
    color: #888;
    font-size: 0.85rem;
    font-weight: 500;
  }

  &.score {
    .stat-icon {
      background: #f6ffed;
      color: #52c41a;
    }
  }

  &.danger {
    .stat-icon {
      background: #fff1f0;
      color: #f5222d;
    }
    .stat-value {
      color: #f5222d;
    }
  }
}

.pm-error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #888;
  text-align: center;

  i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #ff4d4f;
  }
  h3 {
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 600;
  }
}
</style>
