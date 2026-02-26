import { describe, it, expect, vi, beforeEach, type Mock } from 'vitest';
import { ref } from 'vue';
import { useRegister } from '@/composables/useRegister';
import { authService, AuthServiceError } from '@/services/backend/auth';
import { memberService } from '@/services/backend/member';
import type { RegisterRequestInterface } from '@/services/backend/auth/types';
import type { AuthToken } from '@/entities/AuthToken';
import type { Member } from '@/entities/Member';

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
    },
  };
});

vi.mock('@/services/backend/member', () => ({
  memberService: {
    getMe: vi.fn(),
  },
}));

import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useMutation } from '@tanstack/vue-query';

interface MutationResult {
  token: AuthToken;
  member: Member;
}

interface MutationConfig {
  mutationFn: (data: RegisterRequestInterface) => Promise<MutationResult>;
  onSuccess: (data: MutationResult) => void;
}

describe('useRegister', () => {
  let mockSetAuth: Mock;
  let mockSetMember: Mock;
  let mockPush: Mock;
  let mutationConfig: MutationConfig;

  beforeEach(() => {
    vi.clearAllMocks();
    mockSetAuth = vi.fn();
    mockSetMember = vi.fn();
    mockPush = vi.fn();
    mutationConfig = {
      mutationFn: vi.fn(),
      onSuccess: vi.fn(),
    };

    vi.mocked(useAuthStore).mockReturnValue({
      setAuth: mockSetAuth,
      setMember: mockSetMember,
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

  it('should return register, isLoading, and error', () => {
    const { register, isLoading, error } = useRegister();

    expect(register).toBeDefined();
    expect(isLoading.value).toBe(false);
    expect(error.value).toBeNull();
  });

  it('should call authService.register and memberService.getMe with data when mutate is called', async () => {
    const mockAuthToken = { getToken: () => 'test-token' } as AuthToken;
    const mockMember = {
      id: '1',
      full_name: 'John Doe',
      initials: 'JD',
      email: 'john@example.com',
    } as Member;
    vi.mocked(authService.register).mockResolvedValueOnce(mockAuthToken);
    vi.mocked(memberService.getMe).mockResolvedValueOnce(mockMember);

    useRegister();

    const mockData = {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com',
      password: 'password',
      password_confirmation: 'password',
      toFormData: () => new FormData(),
    } as RegisterRequestInterface;

    await mutationConfig.mutationFn(mockData);

    expect(authService.register).toHaveBeenCalledWith(mockData);
    expect(memberService.getMe).toHaveBeenCalledWith('test-token');
  });

  it('should set auth token, member, and redirect on success', () => {
    const mockAuthToken = { getToken: () => 'new-auth-token' } as AuthToken;
    const mockMember = {
      id: '1',
      full_name: 'John Doe',
      initials: 'JD',
      email: 'john@example.com',
    } as Member;

    useRegister();

    mutationConfig.onSuccess({ token: mockAuthToken, member: mockMember });

    expect(mockSetAuth).toHaveBeenCalledWith('new-auth-token');
    expect(mockSetMember).toHaveBeenCalledWith(mockMember);
    expect(mockPush).toHaveBeenCalledWith('/');
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

    const { isLoading } = useRegister();

    expect(isLoading.value).toBe(true);
  });

  it('should expose error when mutation fails', () => {
    const mockError = new AuthServiceError('Validation failed', {
      type: 'ValidationError',
      message: 'Invalid data',
    });

    vi.mocked(useMutation).mockImplementation((config: unknown) => {
      mutationConfig = config as MutationConfig;
      return {
        mutate: vi.fn(),
        isPending: ref(false),
        error: ref(mockError),
      } as unknown as ReturnType<typeof useMutation>;
    });

    const { error } = useRegister();

    expect(error.value).toBe(mockError);
  });
});
