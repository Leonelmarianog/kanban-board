import { useMutation } from '@tanstack/vue-query';
import { computed } from 'vue';
import { authApi } from '@/api/backend/auth';
import type { RegisterFormData } from '@/forms/RegisterFormData';

/**
 * Mutation composable for user registration.
 */
export function useRegister() {
  const mutation = useMutation({
    mutationFn: (data: RegisterFormData) => authApi.register(data.toFormData()),
  });

  return {
    register: mutation.mutate,
    registerAsync: mutation.mutateAsync,
    isLoading: computed(() => mutation.isPending.value),
    isError: computed(() => mutation.isError.value),
    isSuccess: computed(() => mutation.isSuccess.value),
    error: computed(() => mutation.error.value),
    data: computed(() => mutation.data.value),
    reset: mutation.reset,
  };
}
