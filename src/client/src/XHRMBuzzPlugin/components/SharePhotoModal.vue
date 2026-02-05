<!--
/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->

<template>
  <post-modal
    :loading="isLoading"
    :disabled="isDisabled"
    :title="$t('buzz.share_photos')"
    @submit="onSubmit"
    @close="$emit('close', false)"
  >
    <template #header>
      <oxd-buzz-post-input
        v-model="post.text"
        :rules="rules.text"
        :placeholder="$t('buzz.post_placeholder')"
      >
      </oxd-buzz-post-input>
    </template>
    <photo-input v-model="post.photos" />
  </post-modal>
</template>

<script>
import {computed, reactive, toRefs} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import PostModal from '@/XHRMBuzzPlugin/components/PostModal';
import PhotoInput from '@/XHRMBuzzPlugin/components/PhotoInput';
import {shouldNotExceedCharLength} from '@/core/util/validation/rules';
import {OxdBuzzPostInput} from '@ohrm/oxd';

export default {
  name: 'SharePhotoModal',

  components: {
    'post-modal': PostModal,
    'photo-input': PhotoInput,
    'oxd-buzz-post-input': OxdBuzzPostInput,
  },

  props: {
    text: {
      type: String,
      default: null,
    },
  },

  emits: ['close'],

  setup(props, context) {
    const rules = {
      text: [shouldNotExceedCharLength(65530)],
    };
    const http = new APIService(window.appGlobal.baseUrl, '/api/v2/buzz/posts');

    const state = reactive({
      post: {
        text: props.text || null,
        photos: [],
      },
      isLoading: false,
    });

    const onSubmit = () => {
      state.isLoading = true;
      http
        .create({
          type: 'photo',
          text: state.post.text,
          photos: state.post.photos,
        })
        .then(() => context.emit('close', true));
    };

    const isDisabled = computed(() => state.post.photos.length === 0);

    return {
      rules,
      onSubmit,
      isDisabled,
      ...toRefs(state),
    };
  },
};
</script>
