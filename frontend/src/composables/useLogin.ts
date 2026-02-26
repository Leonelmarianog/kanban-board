import { useAuthStore } from '@/stores/auth.ts';
import { useRouter } from 'vue-router';
import { useMutation } from '@tanstack/vue-query';
import { authService, AuthServiceError, type LoginRequestInterface } from '@/services/backend/auth';
import { memberService } from '@/services/backend/member';
import { computed } from 'vue';

export function useLogin() {
  const authStore = useAuthStore();
  const router = useRouter();

  const { mutate, isPending, error } = useMutation({
    mutationFn: async (data: LoginRequestInterface) => {
      const token = await authService.login(data);
      const member = await memberService.getMe(token.getToken());
      return { token, member };
    },

    onSuccess: ({ token, member }) => {
      authStore.setAuth(token.getToken());
      authStore.setMember(member);
      router.push('/');
    },
  });

  return {
    login: mutate,
    isLoading: computed(() => isPending.value),
    error: computed(() => error.value as AuthServiceError | null),
  };
}
