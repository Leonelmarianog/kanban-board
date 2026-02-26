import { describe, it, expect, vi, beforeEach, type Mock } from 'vitest';
import { ref } from 'vue';
import { useLogout } from '@/composables/useLogout';
import { authService, AuthServiceError } from '@/services/backend/auth';

vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(),
}));

vi.mock('vue-router', () => ({
  useRouter: vi.fn(),
}));

vi.mock('@tanstack/vue-query', () => ({
  useMutation: vi.fn(),
}));

vi.mock('@/services/backend/auth', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/services/backend/auth')>();
  return {
    ...actual,
    authService: {
      register: vi.fn(),
      login: vi.fn(),
      logout: vi.fn(),
    },
  };
});

import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useMutation } from '@tanstack/vue-query';

interface MutationConfig {
  mutationFn: () => Promise<void>;
  onSuccess: () => void;
}

describe('useLogout', () => {
  let mockClearAuth: Mock;
  let mockPush: Mock;
  let mockToken: string | null;
  let mutationConfig: MutationConfig;

  beforeEach(() => {
    vi.clearAllMocks();
    mockClearAuth = vi.fn();
    mockPush = vi.fn();
    mockToken = 'valid-token';
    mutationConfig = {
      mutationFn: vi.fn(),
      onSuccess: vi.fn(),
    };

    vi.mocked(useAuthStore).mockReturnValue({
      clearAuth: mockClearAuth,
      token: mockToken,
    } as unknown as ReturnType<typeof useAuthStore>);

    vi.mocked(useRouter).mockReturnValue({
      push: mockPush,
    } as unknown as ReturnType<typeof useRouter>);

    vi.mocked(useMutation).mockImplementation((config: unknown) => {
      mutationConfig = config as MutationConfig;
      return {
        mutate: vi.fn(),
        isPending: ref(false),
        error: ref(null),
      } as unknown as ReturnType<typeof useMutation>;
    });
  });

  it('should return logout, isLoading, and error', () => {
    const { logout, isLoading, error } = useLogout();

    expect(logout).toBeDefined();
    expect(isLoading.value).toBe(false);
    expect(error.value).toBeNull();
  });

  it('should call authService.logout with token when mutate is called', async () => {
    vi.mocked(authService.logout).mockResolvedValueOnce();

    useLogout();

    await mutationConfig.mutationFn();

    expect(authService.logout).toHaveBeenCalledWith('valid-token');
  });

  it('should throw error when no token is present', async () => {
    vi.mocked(useAuthStore).mockReturnValue({
      clearAuth: mockClearAuth,
      token: null,
    } as unknown as ReturnType<typeof useAuthStore>);

    let capturedMutationFn: () => Promise<void> = vi.fn();
    vi.mocked(useMutation).mockImplementation((config: unknown) => {
      capturedMutationFn = (config as MutationConfig).mutationFn;
      return {
        mutate: vi.fn(),
        isPending: ref(false),
        error: ref(null),
      } as unknown as ReturnType<typeof useMutation>;
    });

    useLogout();

    // The mutationFn throws synchronously when no token is present,
    // but useMutation wraps it, so we need to catch it as a sync throw
    expect(() => capturedMutationFn()).toThrow('No authentication token found');
  });

  it('should clear auth and redirect to login on success', () => {
    useLogout();

    mutationConfig.onSuccess();

    expect(mockClearAuth).toHaveBeenCalled();
    expect(mockPush).toHaveBeenCalledWith('/login');
  });

  it('should expose isLoading as true when mutation is pending', () => {
    vi.mocked(useMutation).mockImplementation((config: unknown) => {
      mutationConfig = config as MutationConfig;
      return {
        mutate: vi.fn(),
        isPending: ref(true),
        error: ref(null),
      } as unknown as ReturnType<typeof useMutation>;
    });

    const { isLoading } = useLogout();

    expect(isLoading.value).toBe(true);
  });

  it('should expose error when mutation fails', () => {
    const mockError = new AuthServiceError('Logout failed', {
      type: 'AuthenticationError',
      message: 'Invalid token',
    });

    vi.mocked(useMutation).mockImplementation((config: unknown) => {
      mutationConfig = config as MutationConfig;
      return {
        mutate: vi.fn(),
        isPending: ref(false),
        error: ref(mockError),
      } as unknown as ReturnType<typeof useMutation>;
    });

    const { error } = useLogout();

    expect(error.value).toBe(mockError);
  });
});
