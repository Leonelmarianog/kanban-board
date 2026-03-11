import { describe, expect, it, vi, beforeEach } from 'vitest';
import { handleQueryError, handleMutationError } from '../handlers';
import { ApiError, ApiErrorType } from '@/api/backend/ApiError';
import type { Query } from '@tanstack/vue-query';

vi.mock('vue-toastification', () => ({
  useToast: vi.fn(),
}));

vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(),
}));

vi.mock('@/router', () => ({
  default: {
    push: vi.fn(),
  },
}));

import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

function createApiError(type: ApiErrorType): ApiError {
  return ApiError.fromResponse({
    success: false,
    message: 'Error message',
    status: 401,
    error: {
      type,
      message: 'Error message',
      code: 1,
      timestamp: new Date().toISOString(),
    },
  });
}

function createQuery(isBackgroundRefetch: boolean): Query {
  return {
    state: {
      data: isBackgroundRefetch ? { some: 'data' } : undefined,
    },
  } as Query;
}

describe('handleQueryError', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.spyOn(console, 'log').mockImplementation(() => {});
  });

  describe('when error is an unauthenticated error', () => {
    it('should clear auth, redirect to login, show toast, and log error', () => {
      const mockToast = { error: vi.fn() };
      const mockAuthStore = { clearAuth: vi.fn() };
      vi.mocked(useToast).mockReturnValue(mockToast as unknown as ReturnType<typeof useToast>);
      vi.mocked(useAuthStore).mockReturnValue(
        mockAuthStore as unknown as ReturnType<typeof useAuthStore>,
      );

      const error = createApiError(ApiErrorType.AuthenticationException);
      const query = createQuery(false);

      handleQueryError(error, query);

      expect(mockAuthStore.clearAuth).toHaveBeenCalled();
      expect(router.push).toHaveBeenCalledWith({ name: 'Login' });
      expect(mockToast.error).toHaveBeenCalledWith(
        'Your session has expired. Please log in again.',
      );
      expect(console.log).toHaveBeenCalledWith('Query Error:', error);
    });
  });

  describe('when error occurs during background refetch', () => {
    it('should show toast and log error', () => {
      const mockToast = { error: vi.fn() };
      vi.mocked(useToast).mockReturnValue(mockToast as unknown as ReturnType<typeof useToast>);

      const error = createApiError(ApiErrorType.UnexpectedException);
      const query = createQuery(true);

      handleQueryError(error, query);

      expect(mockToast.error).toHaveBeenCalledWith(
        'An unexpected error occurred. Please try again later.',
      );
      expect(console.log).toHaveBeenCalledWith('Query Error:', error);
    });
  });

  describe('when error occurs during initial fetch', () => {
    it('should log error without showing toast', () => {
      const mockToast = { error: vi.fn() };
      vi.mocked(useToast).mockReturnValue(mockToast as unknown as ReturnType<typeof useToast>);

      const error = createApiError(ApiErrorType.UnexpectedException);
      const query = createQuery(false);

      handleQueryError(error, query);

      expect(mockToast.error).not.toHaveBeenCalled();
      expect(console.log).toHaveBeenCalledWith('Query Error:', error);
    });
  });
});

describe('handleMutationError', () => {
  beforeEach(() => {
    vi.spyOn(console, 'log').mockImplementation(() => {});
  });

  it('should log mutation error', () => {
    const error = new Error('Mutation failed');

    handleMutationError(error);

    expect(console.log).toHaveBeenCalledWith('Mutation Error:', error);
  });
});
