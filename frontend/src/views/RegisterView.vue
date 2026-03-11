<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useRegister } from '@/composables/mutations/useRegister';
import { useValidationErrors } from '@/composables/shared/useValidationErrors';
import { useAuthStore } from '@/stores/auth';
import RegisterForm from '@/components/RegisterForm.vue';
import type { RegisterFormData } from '@/forms/RegisterFormData';
import { useToast } from 'vue-toastification';
import { ApiError } from '@/api/backend/ApiError.ts';

const router = useRouter();
const authStore = useAuthStore();
const { register, isLoading } = useRegister();
const { errors: validationErrors, setErrors } = useValidationErrors();
const toast = useToast();

function handleRegistration(formData: RegisterFormData) {
  register(formData, {
    onSuccess: (data) => {
      authStore.setAuth(data.token);
      router.push({ name: 'Home' });
    },

    onError: (error) => {
      if (ApiError.isValidationError(error)) {
        setErrors(error.validationErrors);
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
          <RegisterForm
            :isLoading="isLoading"
            :errors="validationErrors"
            @save="handleRegistration"
          />
        </div>
      </div>
    </main>
  </div>
</template>
