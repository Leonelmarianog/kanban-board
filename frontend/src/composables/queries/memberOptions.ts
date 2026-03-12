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
        const members = await memberApi.getMe();
        return members[0];
      },
      staleTime: 5 * 60 * 1000,
    }),
};
