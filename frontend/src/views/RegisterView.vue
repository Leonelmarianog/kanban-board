<script setup lang="ts">
import RegisterForm from '@/components/RegisterForm.vue';
import type { RegisterFormData } from '@/forms/RegisterFormData.ts';
import { useRegister } from '@/composables/useRegister.ts';

const { register, isLoading } = useRegister();

const handleRegister = (values: RegisterFormData) => {
  register(values);
};
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          <RegisterForm @save="handleRegister" :isLoading="isLoading" />
        </div>
      </div>
    </main>
  </div>
</template>
