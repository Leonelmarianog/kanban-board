<script setup lang="ts">
import { Form as VeeForm } from 'vee-validate';

defineProps<{
  schema?: Record<string, unknown>;
}>();

const emit = defineEmits<{
  (e: 'submit', values: Record<string, unknown>): void;
}>();

const onSubmit = (values: Record<string, unknown>) => {
  emit('submit', values);
};
</script>

<template>
  <VeeForm :validation-schema="schema" @submit="onSubmit" v-slot="{ errors, values, meta }">
    <slot :errors="errors" :values="values" :meta="meta" />
  </VeeForm>
</template>
