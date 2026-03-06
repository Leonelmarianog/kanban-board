<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useRegister } from '@/composables/mutations/useRegister';
import { useValidationErrors } from '@/composables/shared/useValidationErrors';
import { useAuthStore } from '@/stores/auth';
import RegisterForm from '@/components/RegisterForm.vue';
import type { RegisterFormData } from '@/forms/RegisterFormData';

const router = useRouter();
const authStore = useAuthStore();
const { register, isLoading } = useRegister();
const { errors: validationErrors, setErrors } = useValidationErrors();

function registerAndRedirect(formData: RegisterFormData) {
  register(formData, {
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
          <RegisterForm
            :isLoading="isLoading"
            :errors="validationErrors"
            @save="registerAndRedirect"
          />
        </div>
      </div>
    </main>
  </div>
</template>
