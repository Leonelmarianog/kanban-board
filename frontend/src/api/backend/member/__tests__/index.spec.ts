import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { memberApi } from '../index';
import type { Member } from '@/api/backend/types';
import { backendClient } from '@/api/backend/client';

vi.mock('@/api/backend/client');

describe('memberApi', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('getMe', () => {
    const mockMember: Member = {
      id: 'user-123',
      full_name: 'Test User',
      initials: 'TU',
      email: 'test@example.com',
      avatar_url: 'https://example.com/avatar.png',
      bio: 'Test bio',
    };

    it('should return Member on success', async () => {
      vi.mocked(backendClient).mockResolvedValue(mockMember);

      const result = await memberApi.getMe();

      expect(result).toEqual(mockMember);
    });

    it('should include correct headers', async () => {
      vi.mocked(backendClient).mockResolvedValue(mockMember);

      await memberApi.getMe();

      expect(backendClient).toHaveBeenCalledWith('/v1/members/me', {
        method: 'GET',
        headers: expect.objectContaining({
          Accept: 'application/json',
        }),
      });
    });

    it('should include bearer token as request header if it exists', async () => {
      vi.mocked(backendClient).mockResolvedValue(mockMember);
      localStorage.setItem('authToken', 'my-auth-token');

      await memberApi.getMe();

      expect(backendClient).toHaveBeenCalledWith('/v1/members/me', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          Authorization: 'Bearer my-auth-token',
        },
      });
    });

    it('should not include bearer token as request header if it does not exist', async () => {
      vi.mocked(backendClient).mockResolvedValue(mockMember);

      await memberApi.getMe();

      expect(backendClient).toHaveBeenCalledWith('/v1/members/me', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
      });
    });
  });
});
