import { queryOptions } from '@tanstack/vue-query';
import { memberApi } from '@/api/backend/member';
import { memberKeys } from './queryKeys';

/**
 * Query options for fetching member data.
 */
export const memberOptions = {
  me: () =>
    queryOptions({
      queryKey: memberKeys.me(),
      queryFn: async () => {
        const result = await memberApi.getMe();

        if (!result.success) {
          throw new Error(result.error.message);
        }

        return result.data;
      },
      staleTime: 5 * 60 * 1000,
    }),
};
