<script setup lang="ts">
import { computed } from 'vue';
import type { PaddingOption, VariantOption, WidthOption } from '@/components/CustomButton.vue';
import CustomButton from '@/components/CustomButton.vue';
import { LoaderCircle } from 'lucide-vue-next';

const props = withDefaults(
  defineProps<{
    type?: 'button' | 'submit';
    variant?: VariantOption;
    padding?: PaddingOption;
    width?: WidthOption;
    disabled?: boolean;
    isLoading?: boolean;
  }>(),
  {
    type: 'button',
    variant: 'default',
    padding: 'md',
    width: 'auto',
    disabled: false,
    isLoading: false,
  },
);

const emit = defineEmits<{
  (e: 'click'): void;
}>();

const internalDisabled = computed(() => props.disabled || props.isLoading);

function click() {
  emit('click');
}
</script>

<template>
  <CustomButton
    :type="type"
    :variant="variant"
    :padding="padding"
    :width="width"
    :disabled="internalDisabled"
    @click="click"
  >
    <span class="flex justify-center items-center">
      <LoaderCircle v-if="isLoading" class="animate-spin h-5 w-5 text-current" />
      <span v-else>
        <slot />
      </span>
    </span>
  </CustomButton>
</template>
