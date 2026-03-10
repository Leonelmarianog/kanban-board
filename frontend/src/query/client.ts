import { QueryClient, QueryCache, MutationCache } from '@tanstack/vue-query';
import { handleQueryError, handleMutationError } from './handlers';

export function createAppQueryClient() {
  return new QueryClient({
    queryCache: new QueryCache({ onError: handleQueryError }),
    mutationCache: new MutationCache({ onError: handleMutationError }),
  });
}

export const queryClient = createAppQueryClient();
