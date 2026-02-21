<script setup lang="ts">
import { useAuthStore } from '@/stores/auth.ts';
import { useMutation } from '@tanstack/vue-query';
import { authService } from '@/services/api.ts';
import { useRouter } from 'vue-router';
import RegisterForm from '@/components/RegisterForm.vue';
import type { RegisterFormData } from '@/forms/RegisterFormData.ts';

const authStore = useAuthStore();
const router = useRouter();

const { mutate: register, isPending } = useMutation({
  mutationFn: (data: RegisterFormData) => {
    return authService.register(data);
  },

  onSuccess: (data) => {
    const { token } = data.data;
    authStore.setAuth(token);
    router.push('/');
  },

  onError: (error: unknown) => {
    console.error('Registration error:', error);
  },
});

const handleRegister = (values: RegisterFormData) => {
  register(values);
};
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          {{ isPending ? 'Loading...' : 'Done' }}
          <RegisterForm @save="handleRegister" />
        </div>
      </div>
    </main>
  </div>
</template>
