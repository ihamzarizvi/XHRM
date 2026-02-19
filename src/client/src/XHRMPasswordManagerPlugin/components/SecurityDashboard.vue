<template>
  <div class="security-dashboard">
    <div class="sd-header">
      <h2>Security Dashboard</h2>
      <p>Analyze your vault for weak, reused, and compromised passwords.</p>
    </div>

    <div v-if="!isAnalyzing && !hasAnalyzed" class="sd-start">
      <div class="sd-empty-state">
        <i class="bi bi-shield-check sd-icon-lg"></i>
        <h3>Audit Your Vault</h3>
        <p>
          We will decrypt your passwords locally to check their strength. <br />
          Your passwords never leave your device.
        </p>
        <button class="btn-audit" @click="startAudit">
          Start Security Audit
        </button>
      </div>
    </div>

    <div v-if="isAnalyzing" class="sd-loading">
      <i class="bi bi-arrow-repeat spin"></i>
      <p>Analyzing {{ analyzedCount }} / {{ totalCount }} items...</p>
    </div>

    <div v-if="hasAnalyzed && !isAnalyzing" class="sd-results">
      <!-- Score Card -->
      <div class="sd-score-card" :class="scoreColorClass">
        <div class="score-circle">
          <svg viewBox="0 0 36 36" class="circular-chart">
            <path
              class="circle-bg"
              d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
            />
            <path
              class="circle"
              :stroke-dasharray="overallScore + ', 100'"
              d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
            />
          </svg>
          <div class="score-text">
            <span class="score-value">{{ overallScore }}</span>
            <span class="score-label">Score</span>
          </div>
        </div>
        <div class="score-summary">
          <h3>{{ scoreLabel }}</h3>
          <p>{{ scoreMessage }}</p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="sd-stats-grid">
        <div
          class="stat-card weak"
          :class="{active: filter === 'weak'}"
          @click="filter = 'weak'"
        >
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span class="stat-value">{{ weakItems.length }}</span>
          <span class="stat-label">Weak Passwords</span>
        </div>
        <div
          class="stat-card reused"
          :class="{active: filter === 'reused'}"
          @click="filter = 'reused'"
        >
          <i class="bi bi-recycle"></i>
          <span class="stat-value">{{ reusedItems.length }}</span>
          <span class="stat-label">Reused Passwords</span>
        </div>
        <div class="stat-card safe" @click="filter = 'safe'">
          <i class="bi bi-shield-check"></i>
          <span class="stat-value">{{ safeCount }}</span>
          <span class="stat-label">Safe Items</span>
        </div>
      </div>

      <!-- Item List -->
      <div class="sd-item-list">
        <h4 class="list-title">
          {{ filterTitle }}
        </h4>
        <div v-if="filteredList.length === 0" class="list-empty">
          <i class="bi bi-check2-circle"></i>
          No items found in this category. Great job!
        </div>
        <div v-else class="list-content">
          <div v-for="item in filteredList" :key="item.id" class="sd-item-row">
            <div class="item-info">
              <div class="item-icon">
                <i class="bi bi-key"></i>
              </div>
              <div class="item-details">
                <div class="item-name">{{ item.name }}</div>
                <div class="item-user">{{ item.usernameDecrypted }}</div>
              </div>
            </div>

            <div class="item-meta">
              <span v-if="filter === 'weak'" class="meta-tag tag-weak">
                Strength: {{ item.strengthScore }}/100
              </span>
              <span v-if="filter === 'reused'" class="meta-tag tag-reused">
                Reused {{ item.reuseCount }} times
              </span>
            </div>

            <button class="btn-action" @click="$emit('edit', item)">
              Change Password
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, computed, PropType} from 'vue';
import {SecurityService} from '../services/SecurityService';

export default defineComponent({
  name: 'SecurityDashboard',
  props: {
    items: {type: Array as PropType<any[]>, default: () => []},
  },
  emits: ['edit'],
  setup(props) {
    const isAnalyzing = ref(false);
    const hasAnalyzed = ref(false);
    const analyzedCount = ref(0);
    const totalCount = computed(() => props.items.length);
    const filter = ref('weak'); // weak, reused, safe

    // We start with raw items (props.items). We need a local state to store analysis results.
    const analyzedItems = ref<any[]>([]);

    const startAudit = async () => {
      if (!SecurityService.isVaultUnlocked()) {
        alert('Please unlock your vault first.');
        return;
      }

      isAnalyzing.value = true;
      hasAnalyzed.value = false;
      analyzedCount.value = 0;
      analyzedItems.value = [];

      const tempItems: any[] = [];
      const passwordMap = new Map<string, number>();

      // Decryption & Analysis Loop
      for (const item of props.items) {
        analyzedCount.value++;

        // Skip non-login items (notes, cards might not have passwords)
        if (item.itemType !== 'login' || !item.passwordEncrypted) {
          continue;
        }
        try {
          let itemKey: CryptoKey | undefined = undefined;

          // Get Item Key
          if (item.encryptedItemKey) {
            try {
              const itemKeyRaw = await SecurityService.decrypt(
                item.encryptedItemKey,
              );
              if (itemKeyRaw && itemKeyRaw !== '[Encrypted Data]') {
                itemKey = await SecurityService.importAESKey(itemKeyRaw);
              }
            } catch (e) {
              // ignore key error, try legacy
            }
          }

          // Decrypt Fields
          const [password, username] = await Promise.all([
            SecurityService.decrypt(item.passwordEncrypted, itemKey),
            item.usernameEncrypted
              ? SecurityService.decrypt(item.usernameEncrypted, itemKey)
              : Promise.resolve(''),
          ]);

          // Analyze
          const strength = SecurityService.assessPasswordStrength(password);

          if (password) {
            passwordMap.set(password, (passwordMap.get(password) || 0) + 1);
          }

          tempItems.push({
            ...item,
            passwordDecrypted: password,
            usernameDecrypted: username || item.username || 'Unknown User', // Fallback
            strengthScore: strength,
            isWeak: strength < 60,
          });

          // Small delay to prevent UI freeze
          if (analyzedCount.value % 5 === 0)
            await new Promise((r) => setTimeout(r, 0));
        } catch (e) {
          console.error('Failed to analyze item', item.id, e);
        }
      }

      // Second pass for Reuse detection
      analyzedItems.value = tempItems.map((i) => ({
        ...i,
        isReused:
          i.passwordDecrypted &&
          (passwordMap.get(i.passwordDecrypted) || 0) > 1,
        reuseCount: passwordMap.get(i.passwordDecrypted) || 0,
      }));

      isAnalyzing.value = false;
      hasAnalyzed.value = true;
    };

    const weakItems = computed(() =>
      analyzedItems.value.filter((i) => i.isWeak),
    );
    const reusedItems = computed(() =>
      analyzedItems.value.filter((i) => i.isReused),
    );
    const safeCount = computed(
      () => analyzedItems.value.filter((i) => !i.isWeak && !i.isReused).length,
    );

    const filteredList = computed(() => {
      if (filter.value === 'weak') return weakItems.value;
      if (filter.value === 'reused') return reusedItems.value;
      return analyzedItems.value.filter((i) => !i.isWeak && !i.isReused);
    });

    const filterTitle = computed(() => {
      if (filter.value === 'weak') return 'Weak Passwords (Score < 60)';
      if (filter.value === 'reused') return 'Reused Passwords';
      return 'Safe Items';
    });

    const overallScore = computed(() => {
      if (analyzedItems.value.length === 0) return 100;
      let total = 0;
      analyzedItems.value.forEach((i) => {
        let s = i.strengthScore;
        if (i.isReused) s -= 20; // Penalty for reuse
        total += s;
      });
      return Math.max(0, Math.floor(total / analyzedItems.value.length));
    });

    const scoreLabel = computed(() => {
      const s = overallScore.value;
      if (s >= 80) return 'Excellent!';
      if (s >= 60) return 'Good';
      if (s >= 40) return 'Fair';
      return 'Critical';
    });

    const scoreMessage = computed(() => {
      const s = overallScore.value;
      if (s >= 80) return 'Your vault is very secure.';
      if (s >= 60) return 'You have a few weak spots.';
      if (s >= 40) return 'Time to change some passwords.';
      return 'Your security is at risk.';
    });

    const scoreColorClass = computed(() => {
      const s = overallScore.value;
      if (s >= 80) return 'score-green';
      if (s >= 60) return 'score-yellow';
      if (s >= 40) return 'score-orange';
      return 'score-red';
    });

    return {
      isAnalyzing,
      hasAnalyzed,
      analyzedCount,
      totalCount,
      startAudit,
      overallScore,
      scoreLabel,
      scoreMessage,
      scoreColorClass,
      weakItems,
      reusedItems,
      safeCount,
      filter,
      filteredList,
      filterTitle,
    };
  },
});
</script>

<style scoped lang="scss">
.security-dashboard {
  padding: 24px;
  background: #f8fafc;
  height: 100%;
  overflow-y: auto;
}

.sd-header {
  margin-bottom: 32px;
  h2 {
    font-size: 1.5rem;
    color: #0f172a;
    margin-bottom: 8px;
  }
  p {
    color: #64748b;
  }
}

.sd-empty-state {
  text-align: center;
  padding: 60px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

  .sd-icon-lg {
    font-size: 3rem;
    color: var(--oxd-primary-one-color);
    margin-bottom: 16px;
    display: inline-block;
  }

  h3 {
    margin-bottom: 8px;
    color: #0f172a;
  }

  p {
    color: #64748b;
    margin-bottom: 24px;
    line-height: 1.5;
  }
}

.btn-audit {
  background: var(--oxd-primary-one-color);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;

  &:hover {
    background: var(--oxd-primary-one-darken-5-color);
  }
}

.sd-loading {
  text-align: center;
  padding: 40px;

  .spin {
    font-size: 2rem;
    color: var(--oxd-primary-one-color);
    margin-bottom: 16px;
    display: inline-block;
    animation: spin 1s linear infinite;
  }
}

.sd-score-card {
  background: #fff;
  padding: 24px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 32px;
  margin-bottom: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);

  .score-circle {
    position: relative;
    width: 100px;
    height: 100px;
  }

  .circular-chart {
    display: block;
    margin: 0 auto;
    max-width: 100%;
    max-height: 100%;
  }

  .circle-bg {
    fill: none;
    stroke: #e2e8f0;
    stroke-width: 3.8;
  }

  .circle {
    fill: none;
    stroke-width: 2.8;
    stroke-linecap: round;
    animation: progress 1s ease-out forwards;
  }

  .score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    display: flex;
    flex-direction: column;
  }

  .score-value {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
  }

  .score-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #94a3b8;
  }
}

.sd-score-card.score-green .circle {
  stroke: #10b981;
}
.sd-score-card.score-green .score-value {
  color: #10b981;
}

.sd-score-card.score-yellow .circle {
  stroke: #f59e0b;
}
.sd-score-card.score-yellow .score-value {
  color: #f59e0b;
}

.sd-score-card.score-orange .circle {
  stroke: #f97316;
}
.sd-score-card.score-orange .score-value {
  color: #f97316;
}

.sd-score-card.score-red .circle {
  stroke: #ef4444;
}
.sd-score-card.score-red .score-value {
  color: #ef4444;
}

.sd-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
  }

  &.active {
    border-color: var(--oxd-primary-one-color);
    background: #fff7ed;
  }

  i {
    font-size: 1.5rem;
  }
  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
  }
  .stat-label {
    font-size: 0.8rem;
    color: #64748b;
  }

  &.weak i {
    color: #ef4444;
  }
  &.reused i {
    color: #f59e0b;
  }
  &.safe i {
    color: #10b981;
  }
}

.sd-item-list {
  background: white;
  border-radius: 16px;
  padding: 24px;
}

.list-title {
  margin-top: 0;
  margin-bottom: 16px;
  color: #0f172a;
}

.sd-item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-bottom: 1px solid #f1f5f9;

  &:last-child {
    border-bottom: none;
  }
}

.item-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.item-icon {
  width: 40px;
  height: 40px;
  background: #f1f5f9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
}

.item-name {
  font-weight: 600;
  color: #0f172a;
}
.item-user {
  font-size: 0.85rem;
  color: #64748b;
}

.meta-tag {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-right: 12px;
}

.tag-weak {
  background: #fee2e2;
  color: #ef4444;
}
.tag-reused {
  background: #fef3c7;
  color: #d97706;
}

.btn-action {
  padding: 6px 12px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  &:hover {
    border-color: var(--oxd-primary-one-color);
    color: var(--oxd-primary-one-color);
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
@keyframes progress {
  from {
    stroke-dasharray: 0, 100;
  }
}
</style>
