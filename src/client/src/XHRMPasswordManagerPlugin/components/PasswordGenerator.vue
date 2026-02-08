<template>
  <div class="password-generator">
    <div class="pg-display">
      <input type="text" readonly :value="generatedPassword" />
      <oxd-button ghost @click="copyPassword"
        ><i class="oxd-icon bi-clipboard"></i
      ></oxd-button>
      <oxd-button ghost @click="generate"
        ><i class="oxd-icon bi-arrow-repeat"></i
      ></oxd-button>
    </div>
    <div class="pg-options">
      <label>Length: {{ length }}</label>
      <input
        v-model.number="length"
        type="range"
        min="6"
        max="64"
        @input="generate"
      />

      <div class="pg-checkboxes">
        <label
          ><input v-model="useUpper" type="checkbox" @change="generate" />
          A-Z</label
        >
        <label
          ><input v-model="useLower" type="checkbox" @change="generate" />
          a-z</label
        >
        <label
          ><input v-model="useNumbers" type="checkbox" @change="generate" />
          0-9</label
        >
        <label
          ><input v-model="useSymbols" type="checkbox" @change="generate" />
          !@#</label
        >
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent, ref, onMounted} from 'vue';

export default defineComponent({
  name: 'PasswordGenerator',
  setup() {
    const generatedPassword = ref('');
    const length = ref(16);
    const useUpper = ref(true);
    const useLower = ref(true);
    const useNumbers = ref(true);
    const useSymbols = ref(true);

    const generate = () => {
      let chars = '';
      if (useLower.value) chars += 'abcdefghijklmnopqrstuvwxyz';
      if (useUpper.value) chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      if (useNumbers.value) chars += '0123456789';
      if (useSymbols.value) chars += '!@#$%^&*()_+~`|}{[]:;?><,./-=';

      if (chars === '') return;

      let result = '';
      for (let i = 0; i < length.value; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      generatedPassword.value = result;
    };

    const copyPassword = () => {
      navigator.clipboard.writeText(generatedPassword.value);
      // Toast success
    };

    onMounted(generate);

    return {
      generatedPassword,
      length,
      useUpper,
      useLower,
      useNumbers,
      useSymbols,
      generate,
      copyPassword,
    };
  },
});
</script>

<style scoped>
.password-generator {
  padding: 10px;
  background: #fff;
  border-radius: 4px;
}
.pg-display {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}
.pg-display input {
  flex: 1;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: monospace;
}
</style>
