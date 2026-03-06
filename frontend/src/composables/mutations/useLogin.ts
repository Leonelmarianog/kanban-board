import { useMutation } from '@tanstack/vue-query';
import { computed } from 'vue';
import { authApi } from '@/api/backend/auth';
import type { LoginFormData } from '@/forms/LoginFormData';

/**
 * Mutation composable for user login.
 */
export function useLogin() {
  const mutation = useMutation({
    mutationFn: (data: LoginFormData) => authApi.login(data.toFormData()),
  });

  return {
    login: mutation.mutate,
    loginAsync: mutation.mutateAsync,
    isLoading: computed(() => mutation.isPending.value),
    isError: computed(() => mutation.isError.value),
    isSuccess: computed(() => mutation.isSuccess.value),
    error: computed(() => mutation.error.value),
    data: computed(() => mutation.data.value),
    reset: mutation.reset,
  };
}
