<template>
  <oxd-dialog :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="oxd-text oxd-text--h3">{{ isEdit ? 'Edit Item' : 'Add Item' }}</h3>
    </template>
    
    <div class="oxd-form-row">
       <oxd-input-field v-model="form.name" label="Name" :error-message="errors.name" required />
    </div>
    
    <div class="oxd-form-row">
       <oxd-input-group label="Type" required>
          <select v-model="form.itemType" class="oxd-select-wrapper">
             <option value="login">Login</option>
             <option value="card">Card</option>
             <option value="identity">Identity</option>
             <option value="note">Secure Note</option>
          </select>
       </oxd-input-group>
    </div>

    <div v-if="form.itemType === 'login'">
       <div class="oxd-form-row">
         <oxd-input-field v-model="form.username" label="Username" />
       </div>
       <div class="oxd-form-row">
         <oxd-input-field v-model="form.password" label="Password" type="password" />
       </div>
       <div class="oxd-form-row">
         <oxd-input-field v-model="form.url" label="URL" />
       </div>
       <div class="oxd-form-row">
         <oxd-input-field v-model="form.totpSecret" label="TOTP Secret (Key)" />
       </div>
    </div>
    
    <div class="oxd-form-row">
       <oxd-input-field v-model="form.notes" label="Notes" type="textarea" />
    </div>

    <template #footer>
       <oxd-button @click="$emit('close')" label="Cancel" ghost />
       <oxd-button @click="save" label="Save" />
    </template>
  </oxd-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch, PropType } from 'vue';
import { SecurityService } from '../services/SecurityService';

interface VaultItemFormData {
  id?: number;
  name: string;
  itemType: string;
  username: string;
  password?: string;
  url?: string;
  notes?: string;
  totpSecret?: string;
}

export default defineComponent({
  name: 'VaultItemForm',
  props: {
    isOpen: { type: Boolean, required: true },
    item: { type: Object as PropType<any>, default: null }
  },
  setup(props, { emit }) {
     const form = ref<VaultItemFormData>({
        name: '',
        itemType: 'login',
        username: '',
        password: '',
        url: '',
        notes: ''
     });
     
     const errors = ref<Record<string, string>>({});
     const isEdit = ref(false);

     watch(() => props.item, (newItem) => {
        if (newItem) {
           form.value = { ...newItem };
           if (newItem.usernameEncrypted) form.value.username = SecurityService.decrypt(newItem.usernameEncrypted);
           if (newItem.passwordEncrypted) form.value.password = SecurityService.decrypt(newItem.passwordEncrypted);
           if (newItem.urlEncrypted) form.value.url = SecurityService.decrypt(newItem.urlEncrypted);
           if (newItem.notesEncrypted) form.value.notes = SecurityService.decrypt(newItem.notesEncrypted);
           if (newItem.totpSecretEncrypted) form.value.totpSecret = SecurityService.decrypt(newItem.totpSecretEncrypted);
           isEdit.value = true;
        } else {
           form.value = { name: '', itemType: 'login', username: '', password: '', url: '', notes: '', totpSecret: '' };
           isEdit.value = false;
        }
     }, { immediate: true });

     const save = () => {
        if (!form.value.name) {
           errors.value = { name: 'Name is required' };
           return;
        }
        
        const output = {
           ...form.value,
           usernameEncrypted: SecurityService.encrypt(form.value.username || ''),
           passwordEncrypted: SecurityService.encrypt(form.value.password || ''),
           urlEncrypted: SecurityService.encrypt(form.value.url || ''),
           notesEncrypted: SecurityService.encrypt(form.value.notes || ''),
           totpSecretEncrypted: SecurityService.encrypt(form.value.totpSecret || '')
        };

        emit('save', output);
     };

     return { form, errors, isEdit, save };
  }
});
</script>
