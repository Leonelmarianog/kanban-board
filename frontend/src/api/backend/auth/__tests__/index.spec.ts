import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { authApi } from '../index';
import type { BackendResponse, AuthToken } from '@/api/backend/types';
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
    it('should return successful response with AuthToken', async () => {
      const mockResponse: BackendResponse<AuthToken> = {
        success: true,
        message: 'Login successful',
        status: 200,
        data: { token: 'test-auth-token' },
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');

      const result = await authApi.login(formData);

      expect(result).toEqual({
        success: true,
        message: 'Login successful',
        status: 200,
        data: { token: 'test-auth-token' },
      });
    });
  });

  describe('logout', () => {
    it('should return successful response with empty array', async () => {
      const mockResponse: BackendResponse<[]> = {
        success: true,
        message: 'Logout successful',
        status: 200,
        data: [],
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);

      const result = await authApi.logout();

      expect(result).toEqual({
        success: true,
        message: 'Logout successful',
        status: 200,
        data: [],
      });
    });

    it('should include bearer token as request header if it exists', async () => {
      const mockResponse: BackendResponse<[]> = {
        success: true,
        message: 'Logout successful',
        status: 200,
        data: [],
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);
      localStorage.setItem('authToken', 'my-auth-token');

      await authApi.logout();

      expect(backendClient).toHaveBeenCalledWith('/auth/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: 'Bearer my-auth-token',
        },
      });
    });

    it('should not include bearer token as request header if it does not exist', async () => {
      const mockResponse: BackendResponse<[]> = {
        success: true,
        message: 'Logout successful',
        status: 200,
        data: [],
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);

      await authApi.logout();

      expect(backendClient).toHaveBeenCalledWith('/auth/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      });
    });
  });

  describe('register', () => {
    it('should return successful response with AuthToken', async () => {
      const mockResponse: BackendResponse<AuthToken> = {
        success: true,
        message: 'Registration successful',
        status: 201,
        data: { token: 'test-auth-token' },
      };

      vi.mocked(backendClient).mockResolvedValue(mockResponse);

      const formData = new FormData();
      formData.append('email', 'test@example.com');
      formData.append('password', 'password123');
      formData.append('full_name', 'Test User');

      const result = await authApi.register(formData);

      expect(result).toEqual({
        success: true,
        message: 'Registration successful',
        status: 201,
        data: { token: 'test-auth-token' },
      });
    });
  });
});
