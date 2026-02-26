import { useAuthStore } from '@/stores/auth.ts';
import { useRouter } from 'vue-router';
import { useMutation } from '@tanstack/vue-query';
import { authService, AuthServiceError } from '@/services/backend/auth';
import { computed } from 'vue';

export function useLogout() {
  const authStore = useAuthStore();
  const router = useRouter();

  const { mutate, isPending, error } = useMutation({
    mutationFn: () => {
      const token = authStore.token;
      if (!token) {
        throw new Error('No authentication token found');
      }
      return authService.logout(token);
    },

    onSuccess: () => {
      authStore.clearAuth();
      router.push('/login');
    },
  });

  return {
    logout: mutate,
    isLoading: computed(() => isPending.value),
    error: computed(() => error.value as AuthServiceError | null),
  };
}
