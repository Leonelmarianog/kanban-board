<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useLogin } from '@/composables/mutations/useLogin';
import { useValidationErrors } from '@/composables/shared/useValidationErrors';
import { useAuthStore } from '@/stores/auth';
import LoginForm from '@/components/LoginForm.vue';
import type { LoginFormData } from '@/forms/LoginFormData';
import { useToast } from 'vue-toastification';
import { ApiError } from '@/api/backend/ApiError.ts';

const router = useRouter();
const authStore = useAuthStore();
const { login, isLoading } = useLogin();
const { errors: validationErrors, setErrors } = useValidationErrors();
const toast = useToast();

function loginAndRedirect(formData: LoginFormData) {
  login(formData, {
    onSuccess: (data) => {
      authStore.setAuth(data.token);
      router.push({ name: 'Home' });
    },

    onError: (error: unknown) => {
      if (ApiError.isValidationError(error)) {
        setErrors(error.validationErrors);
        return;
      }

      if (ApiError.isInvalidCredentialsError(error)) {
        toast.error('Username or password incorrect.');
        return;
      }

      toast.error(
        'An issue occurred while performing this action, please try again or contact support.',
      );
    },
  });
}
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          <LoginForm :isLoading="isLoading" :errors="validationErrors" @save="loginAndRedirect" />
        </div>
      </div>
    </main>
  </div>
</template>
