import { describe, it, expect } from 'vitest';
import { AuthServiceError } from '@/services/backend/auth/AuthServiceError';
import { BackendError } from '@/api/backend/BackendError';
import type { BackendErrorResponseInterface } from '@/api/backend/types';
import type { ErrorDataInterface } from '@/services/backend/types';

describe('AuthServiceError', () => {
  describe('Constructor', () => {
    it('should create an instance with message and data', () => {
      const errorData: ErrorDataInterface = {
        type: 'ValidationError',
        message: 'The given data was invalid.',
      };

      const error = new AuthServiceError('Validation failed', errorData);

      expect(error).toBeInstanceOf(AuthServiceError);
      expect(error.message).toBe('Validation failed');
      expect(error.data).toEqual(errorData);
    });

    it('should be an instance of Error', () => {
      const errorData: ErrorDataInterface = {
        type: 'AuthenticationError',
        message: 'Invalid credentials',
      };

      const error = new AuthServiceError('Auth error', errorData);

      expect(error).toBeInstanceOf(Error);
    });

    it('should have the correct name property', () => {
      const errorData: ErrorDataInterface = {
        type: 'SomeError',
        message: 'Some message',
      };

      const error = new AuthServiceError('Test error', errorData);

      expect(error.name).toBe('AuthServiceError');
    });

    it('should handle validation errors in data', () => {
      const errorData: ErrorDataInterface = {
        type: 'ValidationError',
        message: 'The given data was invalid.',
        validationErrors: {
          email: ['The email has already been taken.'],
          password: ['The password must be at least 8 characters.'],
        },
      };

      const error = new AuthServiceError('Validation failed', errorData);

      expect(error.data.validationErrors).toEqual({
        email: ['The email has already been taken.'],
        password: ['The password must be at least 8 characters.'],
      });
    });
  });

  describe('fromBackendError', () => {
    it('should convert BackendError to AuthServiceError', () => {
      const backendErrorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Validation failed',
        status: 422,
        error: {
          type: 'ValidationError',
          message: 'The given data was invalid.',
          code: 422,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', backendErrorData);

      const authServiceError = AuthServiceError.fromBackendError(backendError);

      expect(authServiceError).toBeInstanceOf(AuthServiceError);
      expect(authServiceError.message).toBe('Validation failed');
      expect(authServiceError.data.type).toBe('ValidationError');
      expect(authServiceError.data.message).toBe('The given data was invalid.');
    });

    it('should map validation_errors to validationErrors', () => {
      const backendErrorData: BackendErrorResponseInterface = {
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
      const backendError = new BackendError('Request failed', backendErrorData);

      const authServiceError = AuthServiceError.fromBackendError(backendError);

      expect(authServiceError.data.validationErrors).toEqual({
        email: ['The email has already been taken.'],
        password: ['The password is too short.'],
      });
    });

    it('should handle BackendError without validation_errors', () => {
      const backendErrorData: BackendErrorResponseInterface = {
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
      const backendError = new BackendError('Request failed', backendErrorData);

      const authServiceError = AuthServiceError.fromBackendError(backendError);

      expect(authServiceError.data.validationErrors).toBeUndefined();
    });

    it('should preserve error type and message from backend error', () => {
      const backendErrorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Server error',
        status: 500,
        error: {
          type: 'ServerError',
          message: 'Internal server error occurred',
          code: 500,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', backendErrorData);

      const authServiceError = AuthServiceError.fromBackendError(backendError);

      expect(authServiceError.data.type).toBe('ServerError');
      expect(authServiceError.data.message).toBe('Internal server error occurred');
    });
  });

  describe('Error throwing', () => {
    it('should be throwable and catchable', () => {
      const errorData: ErrorDataInterface = {
        type: 'TestError',
        message: 'Test message',
      };

      expect(() => {
        throw new AuthServiceError('Test error', errorData);
      }).toThrow(AuthServiceError);
    });

    it('should be catchable with try-catch', () => {
      const errorData: ErrorDataInterface = {
        type: 'TestError',
        message: 'Test message',
      };

      try {
        throw new AuthServiceError('Test error', errorData);
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(AuthServiceError);
        // eslint-disable-next-line
        expect((error as AuthServiceError).message).toBe('Test error');
      }
    });
  });
});
