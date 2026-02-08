<template>
  <oxd-dialog :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="oxd-text oxd-text--h3">Share Item</h3>
    </template>

    <div class="oxd-form-row">
      <oxd-input-field
        v-model="email"
        label="User Email"
        placeholder="Enter email to share with"
      />
    </div>

    <div class="oxd-form-row">
      <oxd-input-group label="Permission">
        <select v-model="permission" class="oxd-select-wrapper">
          <option value="read">Read Only</option>
          <option value="write">Read/Write</option>
        </select>
      </oxd-input-group>
    </div>

    <template #footer>
      <oxd-button ghost label="Cancel" @click="$emit('close')" />
      <oxd-button label="Share" @click="share" />
    </template>
  </oxd-dialog>
</template>

<script lang="ts">
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
