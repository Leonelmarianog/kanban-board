<script setup lang="ts">
import * as yup from 'yup';
import DynamicForm from '@/components/DynamicForm.vue';
import CustomButton from '@/components/CustomButton.vue';
import { RegisterFormData } from '@/forms/RegisterFormData.ts';
import { LoaderCircle } from 'lucide-vue-next';
import CustomField from '@/components/CustomField.vue';

defineProps<{
  isLoading: boolean;
}>();

const emit = defineEmits<{
  (e: 'save', values: RegisterFormData): void;
}>();

const schema = yup.object({
  first_name: yup.string().required('First name is required').min(2, 'Too short'),
  last_name: yup.string().required('Last name is required').min(2, 'Too short'),
  email: yup.string().required('Email is required').email('Must be a valid email'),
  password: yup
    .string()
    .required('Password is required')
    .min(8, 'Password must be at least 8 characters'),
  password_confirmation: yup
    .string()
    .required('Password confirmation is required')
    .oneOf([yup.ref('password')], 'Passwords do not match'),
});

const save = (values: Record<string, string>) => {
  const { first_name, last_name, email, password, password_confirmation } = values;

  const registerFormData = new RegisterFormData(
    first_name,
    last_name,
    email,
    password,
    password_confirmation,
  );

  emit('save', registerFormData);
};
</script>

<template>
  <DynamicForm :schema="schema" @submit="save">
    <template #default="{ meta }">
      <div class="space-y-6 bg-neutral-100 rounded-sm shadow-md p-4">
        <h2 class="font-bold text-lg text-center">Sign up to continue</h2>

        <div class="space-y-2">
          <CustomField
            as="input"
            type="text"
            name="first_name"
            placeholder="Enter your first name..."
            label="First Name"
            direction="vertical"
          />

          <CustomField
            as="input"
            type="text"
            name="last_name"
            placeholder="Enter your last name..."
            label="Last Name"
            direction="vertical"
          />

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
            placeholder="Enter a password for your account..."
            label="Password"
            direction="vertical"
          />

          <CustomField
            as="input"
            type="password"
            name="password_confirmation"
            placeholder="Confirm your password..."
            label="Confirm Password"
            direction="vertical"
          />
        </div>

        <div class="space-x-2">
          <CustomButton
            type="submit"
            variant="green"
            width="full"
            :disabled="!meta.valid || isLoading"
          >
            <span class="flex justify-center items-center">
              <LoaderCircle v-if="isLoading" class="animate-spin" />
              <span v-else>Register</span>
            </span>
          </CustomButton>
        </div>
      </div>
    </template>
  </DynamicForm>
</template>
