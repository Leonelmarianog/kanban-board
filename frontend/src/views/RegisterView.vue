<script setup lang="ts">
import RegisterForm from '@/components/RegisterForm.vue';
import type { RegisterFormData } from '@/forms/RegisterFormData.ts';
import { useRegister } from '@/composables/useRegister.ts';
import { watch } from 'vue';

const { register, isLoading, error } = useRegister();

const handleRegister = (values: RegisterFormData) => {
  register(values);
};

watch(error, () => {
  if (error) {
    console.error('Registration error:', error.value);
  }
});
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          <RegisterForm
            @save="handleRegister"
            :isLoading="isLoading"
            :errors="error?.response?.data?.error?.validation_errors"
          />
        </div>
      </div>
    </main>
  </div>
</template>
