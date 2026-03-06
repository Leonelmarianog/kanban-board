<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useLogin } from '@/composables/mutations/useLogin';
import { useValidationErrors } from '@/composables/shared/useValidationErrors';
import { useAuthStore } from '@/stores/auth';
import LoginForm from '@/components/LoginForm.vue';
import type { LoginFormData } from '@/forms/LoginFormData';

const router = useRouter();
const authStore = useAuthStore();
const { login, isLoading } = useLogin();
const { errors: validationErrors, setErrors } = useValidationErrors();

function loginAndRedirect(formData: LoginFormData) {
  login(formData, {
    onSuccess: (result) => {
      if (!result.success) {
        setErrors(result.error.validation_errors);
        return;
      }

      authStore.setAuth(result.data.token);
      router.push({ name: 'Home' });
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
