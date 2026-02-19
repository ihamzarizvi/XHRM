<template>
  <div class="pg-container">
    <div class="pg-display-row">
      <input
        type="text"
        readonly
        :value="generatedPassword"
        class="pg-output"
      />
      <button class="pg-icon-btn copy" title="Copy" @click="copyPassword">
        <i class="bi bi-clipboard"></i>
      </button>
      <button class="pg-icon-btn refresh" title="Regenerate" @click="generate">
        <i class="bi bi-arrow-repeat" :class="{spin: isGenerating}"></i>
      </button>
    </div>

    <div class="pg-strength-bar">
      <div
        class="strength-fill"
        :style="{width: strength + '%', background: strengthColor}"
      ></div>
    </div>
    <div class="pg-strength-text" :style="{color: strengthColor}">
      Strength: {{ strengthLabel }}
    </div>

    <div class="pg-controls">
      <div class="pg-control-row">
        <label>Length: {{ length }}</label>
        <input
          v-model.number="length"
          type="range"
          min="8"
          max="64"
          class="pg-range"
          @input="generate"
        />
      </div>

      <div class="pg-toggles">
        <label class="pg-toggle" title="Uppercase (A-Z)">
          <input v-model="useUpper" type="checkbox" @change="generate" />
          <span>A-Z</span>
        </label>
        <label class="pg-toggle" title="Lowercase (a-z)">
          <input v-model="useLower" type="checkbox" @change="generate" />
          <span>a-z</span>
        </label>
        <label class="pg-toggle" title="Numbers (0-9)">
          <input v-model="useNumbers" type="checkbox" @change="generate" />
          <span>0-9</span>
        </label>
        <label class="pg-toggle" title="Symbols (!@#)">
          <input v-model="useSymbols" type="checkbox" @change="generate" />
          <span>!@#</span>
        </label>
      </div>
    </div>

    <button class="pg-use-btn" @click="usePassword">Use this Password</button>
  </div>
</template>

<script lang="ts">
/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
import {defineComponent, ref, computed, onMounted} from 'vue';
import {SecurityService} from '../services/SecurityService';

export default defineComponent({
  name: 'PasswordGenerator',
  emits: ['select'],
  setup(props, {emit}) {
    const generatedPassword = ref('');
    const length = ref(16);
    const useUpper = ref(true);
    const useLower = ref(true);
    const useNumbers = ref(true);
    const useSymbols = ref(true);
    const isGenerating = ref(false);

    const generate = () => {
      // Ensure at least one option is selected
      if (
        !useUpper.value &&
        !useLower.value &&
        !useNumbers.value &&
        !useSymbols.value
      ) {
        useLower.value = true;
      }

      isGenerating.value = true;
      try {
        generatedPassword.value = SecurityService.generatePassword(
          length.value,
          useUpper.value,
          useLower.value,
          useNumbers.value,
          useSymbols.value,
        );
      } finally {
        setTimeout(() => {
          isGenerating.value = false;
        }, 300);
      }
    };

    const strength = computed(() => {
      return SecurityService.assessPasswordStrength(generatedPassword.value);
    });

    const strengthLabel = computed(() => {
      const s = strength.value;
      if (s < 40) return 'Weak';
      if (s < 70) return 'Fair';
      if (s < 90) return 'Good';
      return 'Strong';
    });

    const strengthColor = computed(() => {
      const s = strength.value;
      if (s < 40) return '#ef4444'; // red
      if (s < 70) return '#f59e0b'; // orange/yellow
      if (s < 90) return '#10b981'; // green
      return '#059669'; // dark green
    });

    const copyPassword = async () => {
      try {
        await navigator.clipboard.writeText(generatedPassword.value);
        // visual feedback handled by parent or toast ideally
      } catch (e) {
        console.error('Failed to copy', e);
      }
    };

    const usePassword = () => {
      emit('select', generatedPassword.value);
    };

    onMounted(generate);

    return {
      generatedPassword,
      length,
      useUpper,
      useLower,
      useNumbers,
      useSymbols,
      isGenerating,
      generate,
      copyPassword,
      usePassword,
      strength,
      strengthLabel,
      strengthColor,
    };
  },
});
</script>

<style scoped lang="scss">
.pg-container {
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.pg-display-row {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.pg-output {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-family: 'Courier New', Courier, monospace;
  font-size: 1rem;
  letter-spacing: 0.5px;
  background: #fff;
  color: #334155;

  &:focus {
    outline: none;
    border-color: var(--oxd-primary-one-color);
  }
}

.pg-icon-btn {
  background: #fff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;

  &:hover {
    background: #f1f5f9;
    color: var(--oxd-primary-one-color);
    border-color: var(--oxd-primary-one-color);
  }
}

.pg-strength-bar {
  height: 4px;
  background: #e2e8f0;
  border-radius: 2px;
  margin-bottom: 4px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  transition: width 0.3s ease, background-color 0.3s ease;
}

.pg-strength-text {
  text-align: right;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 16px;
}

.pg-controls {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.pg-control-row {
  display: flex;
  flex-direction: column;
  gap: 6px;

  label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
  }
}

.pg-range {
  width: 100%;
  accent-color: var(--oxd-primary-one-color);
}

.pg-toggles {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.pg-toggle {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #fff;
  border: 1px solid #e2e8f0;
  padding: 8px;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #475569;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s;

  &:hover {
    border-color: #cbd5e1;
  }

  input {
    accent-color: var(--oxd-primary-one-color);
  }
}

.pg-use-btn {
  width: 100%;
  padding: 10px;
  background: var(--oxd-primary-one-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;

  &:hover {
    background: var(--oxd-primary-one-darken-5-color);
  }
}

.spin {
  animation: spin 0.5s linear;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
