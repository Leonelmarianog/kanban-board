import { useToast } from 'vue-toastification';

import { useAuthStore } from '@/stores/auth';
import router from '@/router';
import { ApiError } from '@/api/backend/ApiError.ts';

// TODO: Refactor for scalable error handling

export function handleQueryError(error: unknown, query: { state: { data: unknown } }): void {
  if (ApiError.isUnauthenticatedError(error)) {
    handleUnauthenticatedError(error);
    return;
  }

  if (isBackgroundRefetch(query)) {
    handleBackgroundRefetchError(error);
    return;
  }

  logError('Query', error);
}

export function handleMutationError(error: unknown): void {
  logError('Mutation', error);
}

// --- Private helpers ---

function handleUnauthenticatedError(error: unknown): void {
  const authStore = useAuthStore();
  const toast = useToast();

  authStore.clearAuth();
  router.push({ name: 'Login' });
  toast.error('Your session has expired. Please log in again.');
  logError('Query', error);
}

function handleBackgroundRefetchError(error: unknown): void {
  const toast = useToast();
  toast.error('An unexpected error occurred. Please try again later.');
  logError('Query', error);
}

function isBackgroundRefetch(query: { state: { data: unknown } }): boolean {
  return query.state.data !== undefined;
}

function logError(context: 'Query' | 'Mutation', error: unknown): void {
  // TODO: Add monitoring (e.g., Sentry, LogRocket)
  console.log(`${context} Error:`, error);
}
