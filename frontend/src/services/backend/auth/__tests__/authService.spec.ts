import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { authService } from '@/services/backend/auth/authService';
import { AuthServiceError } from '@/services/backend/auth/AuthServiceError';
import { BackendError } from '@/api/backend/BackendError';
import type {
  BackendErrorResponseInterface,
  BackendSuccessResponseInterface,
} from '@/api/backend/types';
import type { AuthTokenInterface } from '@/entities/AuthTokenInterface';

vi.mock('@/api/backend', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/api/backend')>();
  return {
    ...actual,
    backendClient: {
      request: vi.fn(),
    },
  };
});

vi.mock('@/entities/AuthToken', () => ({
  AuthToken: {
    create: vi.fn(),
  },
}));

import { backendClient } from '@/api/backend';
import { AuthToken } from '@/entities/AuthToken';

describe('authService', () => {
  const mockFormData = new FormData();
  mockFormData.append('email', 'test@example.com');

  const createMockRegisterRequest = () => ({
    first_name: 'John',
    last_name: 'Doe',
    email: 'test@example.com',
    password: 'password123',
    password_confirmation: 'password123',
    toFormData: () => mockFormData,
  });

  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('register', () => {
    it('should call backendClient with correct parameters', async () => {
      const mockRequest = createMockRegisterRequest();
      const tokenResponse: AuthTokenInterface = { token: 'test-token-123' };
      const successResponse: BackendSuccessResponseInterface<AuthTokenInterface> = {
        success: true,
        message: 'Registration successful',
        status: 201,
        data: tokenResponse,
      };
      const mockAuthToken = { getToken: () => 'test-token-123' };

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(AuthToken.create).mockReturnValueOnce(mockAuthToken as AuthToken);

      await authService.register(mockRequest);

      expect(backendClient.request).toHaveBeenCalledWith('/auth/register', 'POST', mockFormData);
    });

    it('should return AuthToken on successful registration', async () => {
      const mockRequest = createMockRegisterRequest();
      const tokenResponse: AuthTokenInterface = { token: 'test-token-123' };
      const successResponse: BackendSuccessResponseInterface<AuthTokenInterface> = {
        success: true,
        message: 'Registration successful',
        status: 201,
        data: tokenResponse,
      };
      const mockAuthToken = { getToken: () => 'test-token-123' };

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(AuthToken.create).mockReturnValueOnce(mockAuthToken as AuthToken);

      const result = await authService.register(mockRequest);

      expect(AuthToken.create).toHaveBeenCalledWith(tokenResponse);
      expect(result).toBe(mockAuthToken);
    });

    it('should convert BackendError to AuthServiceError', async () => {
      const mockRequest = createMockRegisterRequest();
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
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      await expect(authService.register(mockRequest)).rejects.toThrow(AuthServiceError);
    });

    it('should preserve validation errors from BackendError', async () => {
      const mockRequest = createMockRegisterRequest();
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
            password: ['The password is too short.'],
          },
        },
      };
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      try {
        await authService.register(mockRequest);
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(AuthServiceError);
        // eslint-disable-next-line
        expect((error as AuthServiceError).message).toBe('Validation failed');
        // eslint-disable-next-line
        expect((error as AuthServiceError).data.validationErrors).toEqual({
          email: ['The email has already been taken.'],
          password: ['The password is too short.'],
        });
      }
    });

    it('should re-throw non-BackendError errors', async () => {
      const mockRequest = createMockRegisterRequest();
      const networkError = new Error('Network error');

      vi.mocked(backendClient.request).mockRejectedValueOnce(networkError);

      await expect(authService.register(mockRequest)).rejects.toThrow('Network error');
    });

    it('should re-throw non-Error objects', async () => {
      const mockRequest = createMockRegisterRequest();

      vi.mocked(backendClient.request).mockRejectedValueOnce('Some string error');

      await expect(authService.register(mockRequest)).rejects.toBe('Some string error');
    });
  });

  describe('login', () => {
    const createMockLoginRequest = () => ({
      email: 'test@example.com',
      password: 'password123',
      toFormData: () => mockFormData,
    });

    it('should call backendClient with correct parameters', async () => {
      const mockRequest = createMockLoginRequest();
      const tokenResponse: AuthTokenInterface = { token: 'test-token-123' };
      const successResponse: BackendSuccessResponseInterface<AuthTokenInterface> = {
        success: true,
        message: 'Login successful',
        status: 200,
        data: tokenResponse,
      };
      const mockAuthToken = { getToken: () => 'test-token-123' };

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(AuthToken.create).mockReturnValueOnce(mockAuthToken as AuthToken);

      await authService.login(mockRequest);

      expect(backendClient.request).toHaveBeenCalledWith('/auth/login', 'POST', mockFormData);
    });

    it('should return AuthToken on successful login', async () => {
      const mockRequest = createMockLoginRequest();
      const tokenResponse: AuthTokenInterface = { token: 'test-token-123' };
      const successResponse: BackendSuccessResponseInterface<AuthTokenInterface> = {
        success: true,
        message: 'Login successful',
        status: 200,
        data: tokenResponse,
      };
      const mockAuthToken = { getToken: () => 'test-token-123' };

      vi.mocked(backendClient.request).mockResolvedValueOnce(successResponse);
      vi.mocked(AuthToken.create).mockReturnValueOnce(mockAuthToken as AuthToken);

      const result = await authService.login(mockRequest);

      expect(AuthToken.create).toHaveBeenCalledWith(tokenResponse);
      expect(result).toBe(mockAuthToken);
    });

    it('should convert BackendError to AuthServiceError', async () => {
      const mockRequest = createMockLoginRequest();
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Invalid credentials',
        status: 401,
        error: {
          type: 'AuthenticationError',
          message: 'Invalid credentials',
          code: 401,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      await expect(authService.login(mockRequest)).rejects.toThrow(AuthServiceError);
    });

    it('should preserve validation errors from BackendError', async () => {
      const mockRequest = createMockLoginRequest();
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
            email: ['The email field is required.'],
            password: ['The password field is required.'],
          },
        },
      };
      const backendError = new BackendError('Request failed', errorData);

      vi.mocked(backendClient.request).mockRejectedValueOnce(backendError);

      try {
        await authService.login(mockRequest);
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(AuthServiceError);
        // eslint-disable-next-line
        expect((error as AuthServiceError).message).toBe('Validation failed');
        // eslint-disable-next-line
        expect((error as AuthServiceError).data.validationErrors).toEqual({
          email: ['The email field is required.'],
          password: ['The password field is required.'],
        });
      }
    });

    it('should re-throw non-BackendError errors', async () => {
      const mockRequest = createMockLoginRequest();
      const networkError = new Error('Network error');

      vi.mocked(backendClient.request).mockRejectedValueOnce(networkError);

      await expect(authService.login(mockRequest)).rejects.toThrow('Network error');
    });

    it('should re-throw non-Error objects', async () => {
      const mockRequest = createMockLoginRequest();

      vi.mocked(backendClient.request).mockRejectedValueOnce('Some string error');

      await expect(authService.login(mockRequest)).rejects.toBe('Some string error');
    });
  });
});
