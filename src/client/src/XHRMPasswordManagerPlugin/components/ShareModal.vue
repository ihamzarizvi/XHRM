<template>
  <oxd-dialog :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="oxd-text oxd-text--h3">Share Item</h3>
    </template>

    <div class="oxd-form-row">
      <div class="oxd-input-group oxd-input-field-bottom-space">
        <label class="oxd-label">User Email</label>
        <input
          v-model="email"
          class="oxd-input oxd-input--active"
          placeholder="Enter email to share with"
        />
      </div>
    </div>

    <div class="oxd-form-row">
      <div class="oxd-input-group oxd-input-field-bottom-space">
        <label class="oxd-label">Permission</label>
        <select v-model="permission" class="oxd-select-wrapper">
          <option value="read">Read Only</option>
          <option value="write">Read/Write</option>
        </select>
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
        @click="share"
      >
        Share
      </button>
    </template>
  </oxd-dialog>
</template>

<script lang="ts">
/* eslint-disable no-console */
import {defineComponent, ref} from 'vue';

export default defineComponent({
  name: 'ShareModal',
  props: {
    isOpen: {type: Boolean, required: true},
    itemId: {type: Number, required: true},
  },
  emits: ['close'],
  setup(props, {emit}) {
    const email = ref('');
    const permission = ref('read');

    const share = () => {
      // API call to share would go here
      // Need to lookup user ID by email first usually, or API handles it
      console.log('Sharing', props.itemId, email.value, permission.value);
      emit('close');
    };

    return {email, permission, share};
  },
});
</script>
