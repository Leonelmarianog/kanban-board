<script setup lang="ts">
import LoginForm from '@/components/LoginForm.vue';
import type { LoginFormData } from '@/forms/LoginFormData.ts';
import { useLogin } from '@/composables/useLogin.ts';

const { login, isLoading, error } = useLogin();

const handleLogin = (values: LoginFormData) => {
  login(values);
};
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          <LoginForm
            @save="handleLogin"
            :isLoading="isLoading"
            :errors="error?.data?.validationErrors"
          />
        </div>
      </div>
    </main>
  </div>
</template>
