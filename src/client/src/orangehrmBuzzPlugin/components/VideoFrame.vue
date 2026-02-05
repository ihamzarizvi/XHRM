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
  <div class="XHRM-buzz-video">
    <iframe
      :src="embedURL"
      frameborder="0"
      class="XHRM-buzz-video-frame"
      title="XHRM buzz video frame"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen
    ></iframe>
  </div>
</template>

<script>
export default {
  name: 'VideoFrame',
  props: {
    videoSrc: {
      type: String,
      required: true,
    },
  },
  computed: {
    embedURL() {
      return new String(this.videoSrc).trim();
    },
  },
};
</script>

<style lang="scss" scoped>
.XHRM-buzz-video {
  height: 0;
  width: 100%;
  overflow: hidden;
  position: relative;
  padding-bottom: 56.25%;
  &-frame {
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    position: absolute;
    border-radius: 0.75rem;
  }
}
</style>

