import { useMutation, useQueryClient } from '@tanstack/vue-query';
import { computed } from 'vue';
import { authApi } from '@/api/backend/auth';
import { memberKeys } from '@/composables';

/**
 * Mutation composable for user logout.
 */
export function useLogout() {
  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: () => authApi.logout(),
    onSuccess: () => {
      queryClient.removeQueries({ queryKey: memberKeys.all });
    },
  });

  return {
    logout: mutation.mutate,
    logoutAsync: mutation.mutateAsync,
    isLoading: computed(() => mutation.isPending.value),
    isError: computed(() => mutation.isError.value),
    isSuccess: computed(() => mutation.isSuccess.value),
    error: computed(() => mutation.error.value),
    reset: mutation.reset,
  };
}
