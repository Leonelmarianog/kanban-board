<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from 'vee-validate';
import type { AnyObjectSchema } from 'yup';

const props = defineProps<{
  schema?: AnyObjectSchema;
  initialValues?: Record<string, unknown> | null;
  errors?: Record<string, string[]> | null;
}>();

const emit = defineEmits<{
  (e: 'submit', values: Record<string, unknown>): void;
}>();

const { errors, values, meta, handleSubmit, setErrors } = useForm({
  validationSchema: props.schema,
  initialValues: props.initialValues || undefined,
});

watch(
  () => props.errors,
  (newErrors) => {
    if (newErrors) {
      setErrors(newErrors);
    }
  },
  { immediate: true },
);

const onSubmit = handleSubmit((values) => {
  emit('submit', values as Record<string, unknown>);
});
</script>

<template>
  <form @submit="onSubmit">
    <slot :errors="errors" :values="values" :meta="meta" />
  </form>
</template>
