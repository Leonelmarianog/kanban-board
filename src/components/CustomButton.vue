<script setup lang="ts">
import { computed } from 'vue';

const BASE_STYLE = 'cursor-pointer disabled:cursor-not-allowed rounded-sm py-2 px-6 text-nowrap';

const VARIANTS = {
  default: 'bg-neutral-500 hover:bg-neutral-400 disabled:bg-neutral-400 text-white',
  sky: 'bg-sky-500 hover:bg-sky-400 disabled:bg-sky-400 text-white',
  transparent: 'bg-black/60 hover:bg-black/80 text-white',
  clear: 'bg-black/0 hover:bg-black/20',
};

const emit = defineEmits<{
  (e: 'click'): void;
}>();

const props = withDefaults(
  defineProps<{
    variant?: string;
    fullWidth?: boolean;
  }>(),
  {
    variant: 'default',
    fullWidth: false,
  },
);

const classes = computed(() => {
  const variant = VARIANTS[props.variant as keyof typeof VARIANTS];
  const width = props.fullWidth ? 'w-full' : '';
  return [BASE_STYLE, width, variant];
});

const onClick = () => {
  emit('click');
};
</script>

<template>
  <button :class="classes" @click="onClick">
    <slot />
  </button>
</template>
