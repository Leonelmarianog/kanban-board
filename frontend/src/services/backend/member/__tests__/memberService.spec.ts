import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { memberService } from '@/services/backend/member/memberService';
import { MemberServiceError } from '@/services/backend/member/MemberServiceError';
import { BackendError } from '@/api/backend/BackendError';
import type {
  BackendErrorResponseInterface,
  BackendSuccessResponseInterface,
  MemberJsonInterface,
} from '@/api/backend/types';

vi.mock('@/api/backend', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/api/backend')>();
  return {
    ...actual,
    backendClient: {
      request: vi.fn(),
    },
  };
});

vi.mock('@/entities/Member', () => ({
  Member: {
    create: vi.fn(),
  },
}));

import { backendClient } from '@/api/backend';
import { Member } from '@/entities/Member';

describe('memberService', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('getMe', () => {
    it('should call backendClient with correct parameters', async () => {
      const memberResponse: MemberJsonInterface = {
        id: '1',
        full_name: 'John Doe',
        initials: 'JD',
        email: 'john@example.com',
      };
      const successResponse: BackendSuccessResponseInterface<MemberJsonInterface[]> = {
        success: true,
        message: 'Member found',
        status: 200,
        data: [memberResponse],
      };
      const mockMember = {
        id: '1',
        full_name: 'John Doe',
        initials: 'JD',
        email: 'john@example.com',
      } as Member;

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(Member.create).mockReturnValueOnce(mockMember);

      await memberService.getMe('test-token');

      expect(backendClient.request).toHaveBeenCalledWith('/v1/members/me', 'GET', undefined, {
        headers: { Authorization: 'Bearer test-token' },
      });
    });

    it('should return Member on successful fetch', async () => {
      const memberResponse: MemberJsonInterface = {
        id: '1',
        full_name: 'John Doe',
        initials: 'JD',
        email: 'john@example.com',
        avatar_url: 'https://example.com/avatar.jpg',
        bio: 'Hello world',
      };
      const successResponse: BackendSuccessResponseInterface<MemberJsonInterface[]> = {
        success: true,
        message: 'Member found',
        status: 200,
        data: [memberResponse],
      };
      const mockMember = {
        id: '1',
        full_name: 'John Doe',
        initials: 'JD',
        email: 'john@example.com',
      } as Member;

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(Member.create).mockReturnValueOnce(mockMember);

      const result = await memberService.getMe('test-token');

      expect(Member.create).toHaveBeenCalledWith({
        id: '1',
        full_name: 'John Doe',
        initials: 'JD',
        email: 'john@example.com',
        avatar_url: 'https://example.com/avatar.jpg',
        bio: 'Hello world',
      });
      expect(result).toBe(mockMember);
    });

    it('should convert BackendError to MemberServiceError', async () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Unauthorized',
        status: 401,
        error: {
          type: 'AuthenticationError',
          message: 'Invalid token',
          code: 401,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      await expect(memberService.getMe('invalid-token')).rejects.toThrow(MemberServiceError);
    });

    it('should preserve error details from BackendError', async () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Unauthorized',
        status: 401,
        error: {
          type: 'AuthenticationError',
          message: 'Invalid token',
          code: 401,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      try {
        await memberService.getMe('invalid-token');
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(MemberServiceError);
        // eslint-disable-next-line
        expect((error as MemberServiceError).message).toBe('Unauthorized');
        // eslint-disable-next-line
        expect((error as MemberServiceError).data.type).toBe('AuthenticationError');
      }
    });

    it('should re-throw non-BackendError errors', async () => {
      const networkError = new Error('Network error');

      vi.mocked(backendClient.request).mockRejectedValueOnce(networkError);

      await expect(memberService.getMe('test-token')).rejects.toThrow('Network error');
    });

    it('should re-throw non-Error objects', async () => {
      vi.mocked(backendClient.request).mockRejectedValueOnce('Some string error');

      await expect(memberService.getMe('test-token')).rejects.toBe('Some string error');
    });
  });
});
