import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { authApi } from '../index';
import type { AuthToken } from '@/api/backend/types';
import { backendClient } from '@/api/backend/client';

vi.mock('@/api/backend/client');

describe('authApi', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('login', () => {
    it('should return AuthToken on success', async () => {
      const mockToken: AuthToken = { token: 'test-auth-token' };
      vi.mocked(backendClient).mockResolvedValue(mockToken);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');

      const result = await authApi.login(formData);

      expect(result).toEqual({ token: 'test-auth-token' });
    });

    it('should include correct headers', async () => {
      const mockToken: AuthToken = { token: 'test-auth-token' };
      vi.mocked(backendClient).mockResolvedValue(mockToken);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');

      await authApi.login(formData);

      expect(backendClient).toHaveBeenCalledWith('/auth/login', {
        method: 'POST',
        body: formData,
        headers: {
          Accept: 'application/json',
        },
      });
    });
  });

  describe('logout', () => {
    it('should complete successfully', async () => {
      vi.mocked(backendClient).mockResolvedValue(undefined);

      const result = await authApi.logout();

      expect(result).toBeUndefined();
    });

    it('should include bearer token as request header if it exists', async () => {
      vi.mocked(backendClient).mockResolvedValue(undefined);
      localStorage.setItem('authToken', 'my-auth-token');

      await authApi.logout();

      expect(backendClient).toHaveBeenCalledWith('/auth/logout', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          Authorization: 'Bearer my-auth-token',
        },
      });
    });

    it('should not include bearer token as request header if it does not exist', async () => {
      vi.mocked(backendClient).mockResolvedValue(undefined);

      await authApi.logout();

      expect(backendClient).toHaveBeenCalledWith('/auth/logout', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
        },
      });
    });

    it('should include correct headers', async () => {
      vi.mocked(backendClient).mockResolvedValue(undefined);

      await authApi.logout();

      expect(backendClient).toHaveBeenCalledWith('/auth/logout', {
        method: 'POST',
        headers: expect.objectContaining({
          Accept: 'application/json',
        }),
      });
    });
  });

  describe('register', () => {
    it('should return AuthToken on success', async () => {
      const mockToken: AuthToken = { token: 'test-auth-token' };
      vi.mocked(backendClient).mockResolvedValue(mockToken);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');
      formData.append('full_name', 'Test User');

      const result = await authApi.register(formData);

      expect(result).toEqual({ token: 'test-auth-token' });
    });

    it('should include correct headers', async () => {
      const mockToken: AuthToken = { token: 'test-auth-token' };
      vi.mocked(backendClient).mockResolvedValue(mockToken);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');
      formData.append('full_name', 'Test User');

      await authApi.register(formData);

      expect(backendClient).toHaveBeenCalledWith('/auth/register', {
        method: 'POST',
        body: formData,
        headers: {
          Accept: 'application/json',
        },
      });
    });
  });
});
