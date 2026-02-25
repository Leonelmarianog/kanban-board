import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { BackendClient } from '@/api/backend/BackendClient';
import { BackendError } from '@/api/backend/BackendError';
import { HttpError } from '@/http';
import type { HttpClientInterface } from '@/http';
import type {
  BackendErrorResponseInterface,
  BackendSuccessResponseInterface,
} from '@/api/backend/types';

describe('BackendClient', () => {
  let mockHttpClient: HttpClientInterface;
  let backendClient: BackendClient;

  beforeEach(() => {
    mockHttpClient = {
      request: vi.fn(),
    };
    backendClient = new BackendClient(mockHttpClient);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('Constructor', () => {
    it('should create an instance with the provided HTTP client', () => {
      expect(backendClient).toBeInstanceOf(BackendClient);
    });
  });

  describe('request', () => {
    it('should make a request with Content-Type header', async () => {
      const responseData: BackendSuccessResponseInterface<{ id: number }> = {
        success: true,
        message: 'Success',
        status: 200,
        data: { id: 1 },
      };
      vi.mocked(mockHttpClient.request).mockResolvedValueOnce(responseData);

      const result = await backendClient.request<{ id: number }>('/users', 'GET');

      expect(mockHttpClient.request).toHaveBeenCalledWith('/users', 'GET', undefined, {
        headers: { 'Content-Type': 'application/json' },
      });
      expect(result).toEqual(responseData);
    });

    it('should make a POST request with data', async () => {
      const requestData = { name: 'New User' };
      const responseData: BackendSuccessResponseInterface<{ id: number; name: string }> = {
        success: true,
        message: 'Created',
        status: 201,
        data: { id: 1, name: 'New User' },
      };
      vi.mocked(mockHttpClient.request).mockResolvedValueOnce(responseData);

      const result = await backendClient.request<{ id: number; name: string }>(
        '/users',
        'POST',
        requestData,
      );

      expect(mockHttpClient.request).toHaveBeenCalledWith('/users', 'POST', requestData, {
        headers: { 'Content-Type': 'application/json' },
      });
      expect(result).toEqual(responseData);
    });

    it('should make a PUT request with data', async () => {
      const requestData = { name: 'Updated User' };
      const responseData: BackendSuccessResponseInterface<{ id: number; name: string }> = {
        success: true,
        message: 'Updated',
        status: 200,
        data: { id: 1, name: 'Updated User' },
      };
      vi.mocked(mockHttpClient.request).mockResolvedValueOnce(responseData);

      const result = await backendClient.request<{ id: number; name: string }>(
        '/users/1',
        'PUT',
        requestData,
      );

      expect(mockHttpClient.request).toHaveBeenCalledWith('/users/1', 'PUT', requestData, {
        headers: { 'Content-Type': 'application/json' },
      });
      expect(result).toEqual(responseData);
    });

    it('should make a DELETE request', async () => {
      const responseData: BackendSuccessResponseInterface<null> = {
        success: true,
        message: 'Deleted',
        status: 204,
        data: null,
      };
      vi.mocked(mockHttpClient.request).mockResolvedValueOnce(responseData);

      const result = await backendClient.request<null>('/users/1', 'DELETE');

      expect(mockHttpClient.request).toHaveBeenCalledWith('/users/1', 'DELETE', undefined, {
        headers: { 'Content-Type': 'application/json' },
      });
      expect(result).toEqual(responseData);
    });

    it('should convert HttpError to BackendError', async () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Validation failed',
        status: 422,
        error: {
          type: 'ValidationError',
          message: 'The given data was invalid.',
          code: 422,
          timestamp: '2024-01-01T00:00:00Z',
          validation_errors: {
            email: ['The email has already been taken.'],
          },
        },
      };
      const httpError = new HttpError(422, errorData);
      vi.mocked(mockHttpClient.request).mockRejectedValueOnce(httpError);

      await expect(
        backendClient.request('/users', 'POST', { email: 'test@example.com' }),
      ).rejects.toThrow(BackendError);
    });

    it('should preserve error data when converting HttpError to BackendError', async () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Unauthorized',
        status: 401,
        error: {
          type: 'AuthenticationError',
          message: 'Invalid credentials',
          code: 401,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const httpError = new HttpError(401, errorData);
      vi.mocked(mockHttpClient.request).mockRejectedValueOnce(httpError);

      try {
        await backendClient.request('/protected', 'GET');
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(BackendError);
        // eslint-disable-next-line
        expect((error as BackendError).message).toBe('There was an error with the request.');
        // eslint-disable-next-line
        expect((error as BackendError).data).toEqual(errorData);
      }
    });

    it('should re-throw non-HttpError errors', async () => {
      const networkError = new Error('Network error');
      vi.mocked(mockHttpClient.request).mockRejectedValueOnce(networkError);

      await expect(backendClient.request('/users', 'GET')).rejects.toThrow('Network error');
    });

    it('should re-throw non-Error objects', async () => {
      vi.mocked(mockHttpClient.request).mockRejectedValueOnce('Some string error');

      await expect(backendClient.request('/users', 'GET')).rejects.toBe('Some string error');
    });
  });
});
