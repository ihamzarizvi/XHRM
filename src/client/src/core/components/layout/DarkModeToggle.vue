<template>
  <div class="XHRM-dark-mode-toggle">
    <button
      type="button"
      class="xhrm-theme-toggle-btn"
      :title="
        isDarkMode
          ? $t('general.switch_to_light_mode')
          : $t('general.switch_to_dark_mode')
      "
      @click="toggleDarkMode"
    >
      <svg
        v-if="isDarkMode"
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="XHRM-theme-icon"
      >
        <circle cx="12" cy="12" r="5" />
        <line x1="12" y1="1" x2="12" y2="3" />
        <line x1="12" y1="21" x2="12" y2="23" />
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
        <line x1="1" y1="12" x2="3" y2="12" />
        <line x1="21" y1="12" x2="23" y2="12" />
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
      </svg>
      <svg
        v-else
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="XHRM-theme-icon"
      >
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
      </svg>
    </button>
  </div>
</template>

<script>
import {ref, onMounted} from 'vue';

export default {
  name: 'DarkModeToggle',
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
      if (
        savedTheme === 'dark' ||
        (!savedTheme &&
          window.matchMedia('(prefers-color-scheme: dark)').matches)
      ) {
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

  .xhrm-theme-toggle-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    color: var(--oxd-text-primary);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      background-color: rgba(255, 255, 255, 0.1);
      transform: rotate(15deg) scale(1.1);
    }
  }
}

.XHRM-theme-icon {
  width: 20px;
  height: 20px;
}
</style>
