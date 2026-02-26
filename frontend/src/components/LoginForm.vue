<script setup lang="ts">
import * as yup from 'yup';
import DynamicForm from '@/components/DynamicForm.vue';
import { LoginFormData } from '@/forms/LoginFormData.ts';
import CustomField from '@/components/CustomField.vue';
import LoadingButton from '@/components/LoadingButton.vue';

defineProps<{
  isLoading: boolean;
  errors?: Record<string, string[]>;
}>();

const emit = defineEmits<{
  (e: 'save', values: LoginFormData): void;
}>();

const schema = yup.object({
  email: yup.string().required('The email field is required.').email('Must be a valid email'),
  password: yup
    .string()
    .required('The password field is required.')
    .min(8, 'Password must be at least 8 characters'),
});

const save = (values: Record<string, unknown>) => {
  const { email, password } = values;

  const loginFormData = new LoginFormData(email as string, password as string);

  emit('save', loginFormData);
};
</script>

<template>
  <DynamicForm :schema="schema" @submit="save" :errors="errors">
    <template #default="{ meta }">
      <div class="space-y-6 bg-neutral-100 rounded-sm shadow-md p-4">
        <h2 class="font-bold text-lg text-center">Sign in to continue</h2>

        <div class="space-y-2">
          <CustomField
            as="input"
            type="email"
            name="email"
            placeholder="Enter your email address..."
            label="Email"
            direction="vertical"
          />

          <CustomField
            as="input"
            type="password"
            name="password"
            placeholder="Enter your password..."
            label="Password"
            direction="vertical"
          />
        </div>

        <LoadingButton
          type="submit"
          variant="green"
          width="full"
          :disabled="!meta.valid || isLoading"
          :isLoading="isLoading"
        >
          Login
        </LoadingButton>
      </div>
    </template>
  </DynamicForm>
</template>
