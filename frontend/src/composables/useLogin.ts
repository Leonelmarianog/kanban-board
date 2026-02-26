import { useAuthStore } from '@/stores/auth.ts';
import { useRouter } from 'vue-router';
import { useMutation } from '@tanstack/vue-query';
import { authService, AuthServiceError, type LoginRequestInterface } from '@/services/backend/auth';
import type { AuthToken } from '@/entities/AuthToken.ts';
import { computed } from 'vue';

export function useLogin() {
  const authStore = useAuthStore();
  const router = useRouter();

  const { mutate, isPending, error } = useMutation({
    mutationFn: (data: LoginRequestInterface) => authService.login(data),

    onSuccess: (data: AuthToken) => {
      authStore.setAuth(data.getToken());
      router.push('/');
    },
  });

  return {
    login: mutate,
    isLoading: computed(() => isPending.value),
    error: computed(() => error.value as AuthServiceError | null),
  };
}
