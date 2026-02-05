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
  <div class="XHRM-buzz-post-actions">
    <post-like :like="post.liked" @click="onClickAction('like')"></post-like>
    <post-comment @click="onClickAction('comment')"></post-comment>
    <post-share @click="onClickAction('share')"></post-share>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import useBuzzAPIs from '@/XHRMBuzzPlugin/util/composable/useBuzzAPIs';
import PostLikeButton from '@/XHRMBuzzPlugin/components/PostLikeButton.vue';
import PostShareButton from '@/XHRMBuzzPlugin/components/PostShareButton.vue';
import PostCommentButton from '@/XHRMBuzzPlugin/components/PostCommentButton.vue';

export default {
  name: 'PostActions',

  components: {
    'post-like': PostLikeButton,
    'post-share': PostShareButton,
    'post-comment': PostCommentButton,
  },

  props: {
    post: {
      type: Object,
      required: true,
    },
  },

  emits: ['like', 'comment', 'share'],

  setup(props, context) {
    let loading = false;
    const {updatePostLike} = useBuzzAPIs(
      new APIService(window.appGlobal.baseUrl, ''),
    );

    const onClickAction = (actionType) => {
      switch (actionType) {
        case 'comment':
          context.emit('comment');
          break;

        case 'share':
          context.emit('share');
          break;

        case 'like':
          if (!loading) {
            loading = true;
            updatePostLike(props.post.id, props.post.liked).then(() => {
              loading = false;
              context.emit('like');
            });
          }
          break;

        default:
          break;
      }
    };

    return {
      onClickAction,
    };
  },
};
</script>

<style lang="scss" scoped>
.XHRM-buzz-post-actions {
  gap: 5px;
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: space-between;
  ::v-deep(.oxd-icon-button) {
    width: 36px;
    height: 36px;
  }
}
</style>

