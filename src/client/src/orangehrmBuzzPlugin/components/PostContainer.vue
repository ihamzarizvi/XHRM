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
  <oxd-sheet :gutters="false" type="white" class="XHRM-buzz">
    <div class="XHRM-buzz-post">
      <div class="XHRM-buzz-post-header">
        <div class="XHRM-buzz-post-header-details">
          <profile-image :employee="post.employee"></profile-image>
          <div class="XHRM-buzz-post-header-text">
            <oxd-text tag="p" class="XHRM-buzz-post-emp-name">
              {{ employeeFullName }}
            </oxd-text>
            <oxd-text tag="p" class="XHRM-buzz-post-time">
              {{ postDateTime }}
            </oxd-text>
          </div>
        </div>
        <div
          v-if="post.permission.canUpdate || post.permission.canDelete"
          class="XHRM-buzz-post-header-config"
        >
          <oxd-dropdown>
            <oxd-icon-button name="three-dots" :with-container="true" />
            <template #content>
              <li
                v-if="post.permission.canDelete"
                class="XHRM-buzz-post-header-config-item"
                @click="$emit('delete', $event)"
              >
                <oxd-icon name="trash" />
                <oxd-text tag="p">
                  {{ $t('buzz.delete_post') }}
                </oxd-text>
              </li>
              <li
                v-if="post.permission.canUpdate"
                class="XHRM-buzz-post-header-config-item"
                @click="$emit('edit', $event)"
              >
                <oxd-icon name="pencil" />
                <oxd-text tag="p">
                  {{ $t('buzz.edit_post') }}
                </oxd-text>
              </li>
            </template>
          </oxd-dropdown>
        </div>
      </div>
      <oxd-divider />
    </div>
    <div class="XHRM-buzz-post-body">
      <slot name="content"></slot>
      <slot name="body"></slot>
    </div>
    <div class="XHRM-buzz-post-footer">
      <slot name="actionButton"></slot>
      <slot name="postStats"></slot>
    </div>
    <slot name="comments"></slot>
  </oxd-sheet>
</template>

<script>
import {computed} from 'vue';
import useLocale from '@/core/util/composable/useLocale';
import useDateFormat from '@/core/util/composable/useDateFormat';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import ProfileImage from '@/XHRMBuzzPlugin/components/ProfileImage';
import useEmployeeNameTranslate from '@/core/util/composable/useEmployeeNameTranslate';
import {OxdDropdownMenu, OxdIcon, OxdSheet} from '@ohrm/oxd';

export default {
  name: 'PostContainer',
  components: {
    'oxd-icon': OxdIcon,
    'oxd-sheet': OxdSheet,
    'oxd-dropdown': OxdDropdownMenu,
    'profile-image': ProfileImage,
  },
  props: {
    post: {
      type: Object,
      required: true,
    },
  },

  emits: ['edit', 'delete'],

  setup(props) {
    const {locale} = useLocale();
    const {jsDateFormat, jsTimeFormat} = useDateFormat();
    const {$tEmpName} = useEmployeeNameTranslate();

    const employeeFullName = computed(() => {
      return $tEmpName(props.post.employee, {
        includeMiddle: true,
        excludePastEmpTag: false,
      });
    });

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

    return {
      postDateTime,
      employeeFullName,
    };
  },
};
</script>

<style lang="scss" scoped src="./post-container.scss"></style>

