import { useMutation } from '@tanstack/vue-query';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { authService } from '@/services/backend/auth';
import type { RegisterFormData } from '@/forms/RegisterFormData';
import type { AuthToken } from '@/entities/AuthToken';

export function useRegister() {
  const authStore = useAuthStore();
  const router = useRouter();

  const { mutate, isPending } = useMutation({
    mutationFn: (data: RegisterFormData) => authService.register(data),

    onSuccess: (data: AuthToken) => {
      authStore.setAuth(data.getToken());
      router.push('/');
    },

    onError: (err: unknown) => {
      console.error('Registration error:', err);
    },
  });

  return {
    register: mutate,
    isLoading: isPending as boolean,
  };
}
