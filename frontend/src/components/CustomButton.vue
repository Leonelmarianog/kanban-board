<script setup lang="ts">
import { computed } from 'vue';

export type VariantOption = 'default' | 'green' | 'transparent' | 'clear';
export type PaddingOption = 'xs' | 'sm' | 'md' | 'lg';
export type WidthOption = 'auto' | 'full';

const emit = defineEmits<{
  (e: 'click'): void;
}>();

const props = withDefaults(
  defineProps<{
    variant?: VariantOption;
    padding?: PaddingOption;
    width?: WidthOption;
  }>(),
  {
    variant: 'default',
    padding: 'md',
    width: 'auto',
  },
);

const BASE_STYLE = 'cursor-pointer disabled:cursor-not-allowed rounded-sm text-nowrap';

const VARIANT_OPTIONS: Record<VariantOption, string> = {
  default: 'bg-neutral-600 hover:bg-neutral-700 disabled:bg-neutral-700 text-white',
  green: 'bg-green-600 hover:bg-green-700 disabled:bg-green-700 text-white',
  transparent: 'bg-black/40 hover:bg-black/60 disabled:bg-black/60 text-white',
  clear: 'bg-transparent hover:bg-black/10 disabled:bg-bg-black/10 text-neutral-600',
};

const PADDING_OPTIONS: Record<PaddingOption, string> = {
  xs: 'py-1 px-1',
  sm: 'py-1 px-2',
  md: 'py-2 px-3',
  lg: 'py-3 px-4',
};

const WIDTH_OPTIONS: Record<WidthOption, string> = {
  auto: 'auto',
  full: 'w-full',
};

const classes = computed(() => {
  const variant = VARIANT_OPTIONS[props.variant];
  const padding = PADDING_OPTIONS[props.padding];
  const width = WIDTH_OPTIONS[props.width];
  return [BASE_STYLE, variant, padding, width];
});

function click() {
  emit('click');
}
</script>

<template>
  <button :class="classes" @click="click">
    <slot />
  </button>
</template>
