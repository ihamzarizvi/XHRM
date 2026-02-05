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
  <div class="XHRM-label-link-wrapper">
    <oxd-text class="XHRM-text-label" tag="p">{{ label }} : </oxd-text>
    <a :href="url" target="_blank" class="XHRM-vacancy-link">
      {{ url }}
    </a>
  </div>
</template>

<script>
export default {
  name: 'VacancyLinkCard',
  props: {
    label: {
      type: String,
      required: true,
    },
    url: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss" scoped>
.XHRM-label-link-wrapper {
  display: flex;
  flex-wrap: wrap;
  &:first-of-type {
    margin-bottom: 0.25rem;
  }
}
.XHRM-text-label {
  font-size: 12px;
  font-weight: 600;
  padding-right: 0.2rem;
  color: $oxd-interface-gray-darken-1-color;
}
.XHRM-vacancy-link {
  font-size: 12px;
  font-weight: 600;
  margin-left: 1rem;
  text-decoration: none;
  word-break: break-all;
  font-family: $oxd-font-family;
  color: $oxd-primary-one-color;
}
</style>

