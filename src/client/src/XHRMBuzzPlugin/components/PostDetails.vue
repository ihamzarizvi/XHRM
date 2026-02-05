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
  <div class="XHRM-post-details">
    <oxd-icon-button
      class="XHRM-post-details-close"
      name="x"
      :with-container="false"
      @click="onClickClose"
    />
    <div class="XHRM-post-details-header">
      <profile-image :employee="post.employee"></profile-image>
      <div class="XHRM-post-details-header-text">
        <oxd-text tag="p" class="XHRM-post-details-emp-name">
          {{ employeeFullName }}
        </oxd-text>
        <oxd-text tag="p" class="XHRM-post-details-time">
          {{ postDateTime }}
        </oxd-text>
      </div>
    </div>
    <oxd-text v-if="post.text" tag="p" :class="postClasses">
      {{ post.text }}
    </oxd-text>
    <oxd-text
      v-show="!readMore"
      tag="p"
      class="XHRM-post-details-readmore"
      @click="onClickReadMore"
    >
      {{ $t('buzz.read_more') }}
    </oxd-text>
    <oxd-divider></oxd-divider>
    <div class="XHRM-post-details-actions">
      <post-like :like="post.liked" @click="onClickLike"></post-like>
      <post-stats :post="post" :mobile="mobile"></post-stats>
    </div>
    <oxd-divider></oxd-divider>
    <post-comment-container
      :post-id="post.id"
      :employee="post.employee"
      @create="$emit('createComment', $event)"
      @delete="$emit('deleteComment', $event)"
    ></post-comment-container>
  </div>
</template>

<script>
import {computed, ref} from 'vue';
import useLocale from '@/core/util/composable/useLocale';
import {APIService} from '@/core/util/services/api.service';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import useDateFormat from '@/core/util/composable/useDateFormat';
import PostStats from '@/XHRMBuzzPlugin/components/PostStats';
import ProfileImage from '@/XHRMBuzzPlugin/components/ProfileImage';
import useBuzzAPIs from '@/XHRMBuzzPlugin/util/composable/useBuzzAPIs';
import PostLikeButton from '@/XHRMBuzzPlugin/components/PostLikeButton';
import useEmployeeNameTranslate from '@/core/util/composable/useEmployeeNameTranslate';
import PostCommentContainer from '@/XHRMBuzzPlugin/components/PostCommentContainer';

export default {
  name: 'PostDetails',

  components: {
    'post-stats': PostStats,
    'post-like': PostLikeButton,
    'profile-image': ProfileImage,
    'post-comment-container': PostCommentContainer,
  },

  props: {
    post: {
      type: Object,
      required: true,
    },
    mobile: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['like', 'close', 'createComment', 'deleteComment'],

  setup(props, context) {
    let loading = false;
    const {locale} = useLocale();
    const {jsDateFormat, jsTimeFormat} = useDateFormat();
    const {$tEmpName} = useEmployeeNameTranslate();
    const readMore = ref(new String(props.post?.text).length < 500);
    const {updatePostLike} = useBuzzAPIs(
      new APIService(window.appGlobal.baseUrl, ''),
    );

    const postDateTime = computed(() => {
      const {createdDate, createdTime} = props.post;

      const utcDate = parseDate(
        `${createdDate} ${createdTime} +00:00`,
        'yyyy-MM-dd HH:mm xxx',
      );

      return formatDate(utcDate, `${jsDateFormat} ${jsTimeFormat}`, {
        locale,
      });
    });

    const employeeFullName = computed(() => {
      return $tEmpName(props.post.employee, {
        includeMiddle: true,
        excludePastEmpTag: false,
      });
    });

    const onClickClose = () => context.emit('close');

    const onClickLike = () => {
      if (!loading) {
        loading = true;
        updatePostLike(props.post.id, props.post.liked).then(() => {
          loading = false;
          context.emit('like');
        });
      }
    };

    const postClasses = computed(() => ({
      'XHRM-post-details-text': true,
      '--truncate': readMore.value === false,
    }));

    const onClickReadMore = () => {
      readMore.value = !readMore.value;
    };

    return {
      readMore,
      postClasses,
      onClickLike,
      onClickClose,
      postDateTime,
      onClickReadMore,
      employeeFullName,
    };
  },
};
</script>

<style src="./post-details.scss" lang="scss" scoped></style>
