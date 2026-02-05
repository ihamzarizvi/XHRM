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
  <oxd-input-field type="file">
    <div class="XHRM-photo-upload-area">
      <oxd-icon class="XHRM-photo-upload-icon" name="images" />
      <oxd-text type="card-title">
        {{ $t('buzz.add_photos') }}
      </oxd-text>
    </div>
  </oxd-input-field>
</template>

<script>
import {OxdIcon} from '@ohrm/oxd';

export default {
  name: 'PhotoUploadArea',

  components: {
    'oxd-icon': OxdIcon,
  },
};
</script>

<style src="./photo-upload-area.scss" lang="scss" scoped></style>
