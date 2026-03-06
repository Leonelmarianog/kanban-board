import { useQuery } from '@tanstack/vue-query';
import { useAuthStore } from '@/stores/auth';
import { memberOptions } from '@/composables';

/**
 * Query composable for fetching the current authenticated member.
 * @returns The query object containing the member data.
 */
export function useMeQuery() {
  const authStore = useAuthStore();

  return useQuery({
    ...memberOptions.me(),
    enabled: () => authStore.isAuthenticated,
  });
}
