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
  <div class="XHRM-background-container">
    <div class="XHRM-card-container">
      <oxd-text tag="h6" class="XHRM-main-title">
        {{ $t('help.getting_started_with_XHRM') }}
      </oxd-text>
      <oxd-divider />
      <oxd-text tag="p" class="XHRM-support-text">
        {{ $t('help.getting_started_with_XHRM_message_1') }}
      </oxd-text>
      <br />
      <oxd-text tag="p" class="XHRM-support-text">
        {{ $t('help.getting_started_with_XHRM_message_2') }}
      </oxd-text>
      <div class="XHRM-support">
        <oxd-grid :cols="2" class="XHRM-support-channel --mail">
          <oxd-grid-item>
            <img :src="helpImgSrc" alt="help image" />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-text class="XHRM-sub-title" tag="p">
              {{ $t('help.customer_support') }}
            </oxd-text>
            <oxd-text tag="p" class="XHRM-support-text">
              {{ $t('help.contact_us_on_message') }}
              <a
                class="XHRM-support-link"
                href="mailto:ossupport@XHRM.com"
              >
                ossupport@XHRM.com
              </a>
              {{ $t('help.we_will_be_delighted_to_help') }}
            </oxd-text>
          </oxd-grid-item>
        </oxd-grid>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      helpImgSrc: `${window.appGlobal.publicPath}/images/help.png`,
    };
  },
};
</script>

<style lang="scss" scoped>
.XHRM-support-text {
  font-size: 12px;
}
.XHRM-support {
  padding: 1rem 0;
  &-channel {
    grid-template-columns: 50px 1fr;
    align-items: center;
    img {
      width: 40px;
      height: 40px;
    }
  }
  &-link {
    text-decoration: none;
    color: $oxd-primary-one-color;
  }
}
</style>

