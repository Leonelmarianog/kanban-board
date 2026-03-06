import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { memberApi } from '../index';
import type { BackendResponse, Member } from '@/api/backend/types';
import { backendClient } from '@/api/backend/client';

vi.mock('@/api/backend/client');

describe('memberApi', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('getMe', () => {
    it('should return successful response with Member', async () => {
      const mockResponse: BackendResponse<Member> = {
        success: true,
        message: 'Member retrieved successfully',
        status: 200,
        data: {
          id: 'user-123',
          full_name: 'Test User',
          initials: 'TU',
          email: 'test@example.com',
          avatar_url: 'https://example.com/avatar.png',
          bio: 'Test bio',
        },
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);

      const result = await memberApi.getMe();

      expect(result).toEqual({
        success: true,
        message: 'Member retrieved successfully',
        status: 200,
        data: {
          id: 'user-123',
          full_name: 'Test User',
          initials: 'TU',
          email: 'test@example.com',
          avatar_url: 'https://example.com/avatar.png',
          bio: 'Test bio',
        },
      });
    });
  });
});
