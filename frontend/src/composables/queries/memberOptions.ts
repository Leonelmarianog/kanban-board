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
      queryFn: () => memberApi.getMe(),
      staleTime: 5 * 60 * 1000,
    }),
};
