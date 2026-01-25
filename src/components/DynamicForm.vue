<script setup lang="ts">
import { Form as VeeForm } from 'vee-validate';
import type { AnyObjectSchema } from 'yup';

defineProps<{
  schema?: AnyObjectSchema;
  initialValues?: Record<string, unknown> | null;
}>();

const emit = defineEmits<{
  (e: 'submit', values: Record<string, unknown>): void;
}>();

const onSubmit = (values: Record<string, unknown>) => {
  emit('submit', values as Record<string, unknown>);
};
</script>

<template>
  <VeeForm
    :validation-schema="schema"
    :initial-values="initialValues || undefined"
    @submit="onSubmit"
    v-slot="{ errors, values, meta }"
  >
    <slot :errors="errors" :values="values" :meta="meta" />
  </VeeForm>
</template>
