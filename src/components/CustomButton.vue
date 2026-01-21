<script lang="ts">
import { defineComponent } from 'vue';

const BASE_STYLE = 'cursor-pointer disabled:cursor-not-allowed rounded-sm py-2 px-6';

const VARIANTS = {
  default: 'bg-neutral-500 hover:bg-neutral-400 disabled:bg-neutral-400 text-white',
  sky: 'bg-sky-500 hover:bg-sky-400 disabled:bg-sky-400 text-white',
};

export default defineComponent({
  emits: ['click'],

  props: {
    variant: {
      type: String,
      default: 'default',
    },
  },

  computed: {
    classes() {
      const variant = VARIANTS[this.variant as keyof typeof VARIANTS];
      return [BASE_STYLE, variant];
    },
  },

  methods: {
    onClick() {
      this.$emit('click');
    },
  },
});
</script>

<template>
  <button :class="classes" @click="onClick">
    <slot />
  </button>
</template>
