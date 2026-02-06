<template>
  <div class="XHRM-dark-mode-toggle">
    <button
      type="button"
      class="oxd-icon-button"
      @click="toggleDarkMode"
      :title="isDarkMode ? $t('general.switch_to_light_mode') : $t('general.switch_to_dark_mode')"
    >
      <oxd-icon
        type="svg"
        :name="isDarkMode ? 'sun' : 'moon'"
        class="XHRM-theme-icon"
      ></oxd-icon>
    </button>
  </div>
</template>

<script>
import {ref, onMounted} from 'vue';
import {OxdIcon} from '@ohrm/oxd';

export default {
  name: 'DarkModeToggle',
  components: {
    'oxd-icon': OxdIcon,
  },
  setup() {
    const isDarkMode = ref(false);

    const applyTheme = (dark) => {
      isDarkMode.value = dark;
      if (dark) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('xhrm-theme', 'dark');
      } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('xhrm-theme', 'light');
      }
    };

    const toggleDarkMode = () => {
      applyTheme(!isDarkMode.value);
    };

    onMounted(() => {
      const savedTheme = localStorage.getItem('xhrm-theme');
      if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        applyTheme(true);
      }
    });

    return {
      isDarkMode,
      toggleDarkMode,
    };
  },
};
</script>

<style lang="scss" scoped>
.XHRM-dark-mode-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;

  .oxd-icon-button {
    color: var(--oxd-text-primary);
    transition: transform 0.3s ease;
    
    &:hover {
      transform: rotate(15deg) scale(1.1);
    }
  }
}

.XHRM-theme-icon {
  width: 20px;
  height: 20px;
}
</style>
