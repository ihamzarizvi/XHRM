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
  <div class="XHRM-upgrade-container">
    <a
      class="XHRM-upgrade-link"
      target="_blank"
      href="https://XHRM.com/open-source/upgrade-to-advanced"
    >
      <oxd-glass-button
        :label="$t('general.upgrade')"
        size="large"
        class="XHRM-upgrade-button"
      >
        <template #icon>
          <oxd-icon
            type="svg"
            name="upgrade"
            class="XHRM-upgrade-icon"
          ></oxd-icon>
        </template>
      </oxd-glass-button>
    </a>
  </div>
</template>

<script>
import {OxdGlassButton, OxdIcon} from '@ohrm/oxd';

export default {
  name: 'UpgradeButton',
  components: {
    'oxd-icon': OxdIcon,
    'oxd-glass-button': OxdGlassButton,
  },
};
</script>

<style src="./upgrade-button.scss" scoped lang="scss"></style>

