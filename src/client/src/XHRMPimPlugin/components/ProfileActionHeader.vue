<template>
  <div class="XHRM-action-header">
    <oxd-text tag="h6" class="XHRM-main-title">
      <slot></slot>
    </oxd-text>
    <oxd-button
      v-if="actionButtonShown"
      :label="$t('general.add')"
      icon-name="plus"
      display-type="text"
      v-bind="$attrs"
    />
  </div>
</template>

<script>
export default {
  name: 'ProfileActionHeader',
  inheritAttrs: false,
  props: {
    actionButtonShown: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
};
</script>

<style lang="scss" scoped>
.XHRM-action {
  &-header {
    display: flex;
    overflow-wrap: break-word;
    align-items: center;
    button {
      margin-left: 1rem;
      white-space: nowrap;
    }
  }
}
</style>
