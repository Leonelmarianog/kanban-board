<script setup lang="ts">
import type { Label } from '@/types';

defineProps<{
  label: Label;
}>();
</script>

<template>
  <div
    class="text-xs capitalize text-white py-px px-2 rounded-sm inline-block"
    :style="{ backgroundColor: label.color }"
  >
    {{ label.name }}
  </div>
</template>
