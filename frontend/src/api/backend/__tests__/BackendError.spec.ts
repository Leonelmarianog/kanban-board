import { describe, it, expect } from 'vitest';
import { BackendError } from '@/api/backend/BackendError';
import type { BackendErrorResponseInterface } from '@/api/backend/types';

describe('BackendError', () => {
  describe('Constructor', () => {
    it('should create an instance with message and data', () => {
      const errorData: BackendErrorResponseInterface = {
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

      const error = new BackendError('Custom error message', errorData);

      expect(error).toBeInstanceOf(BackendError);
      expect(error.message).toBe('Custom error message');
      expect(error.data).toEqual(errorData);
    });

    it('should be an instance of Error', () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Not found',
        status: 404,
        error: {
          type: 'NotFoundError',
          message: 'Resource not found',
          code: 404,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };

      const error = new BackendError('Not found', errorData);

      expect(error).toBeInstanceOf(Error);
    });

    it('should have the correct name property', () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Server error',
        status: 500,
        error: {
          type: 'ServerError',
          message: 'Internal server error',
          code: 500,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };

      const error = new BackendError('Server error', errorData);

      expect(error.name).toBe('BackendError');
    });
  });

  describe('Error data structure', () => {
    it('should handle validation errors in data', () => {
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
            password: ['The password must be at least 8 characters.'],
          },
        },
      };

      const error = new BackendError('Validation failed', errorData);

      expect(error.data.error.validation_errors).toEqual({
        email: ['The email has already been taken.'],
        password: ['The password must be at least 8 characters.'],
      });
    });

    it('should handle error data without validation_errors', () => {
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

      const error = new BackendError('Unauthorized', errorData);

      expect(error.data.error.validation_errors).toBeUndefined();
    });
  });

  describe('Error throwing', () => {
    it('should be throwable and catchable', () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Error',
        status: 400,
        error: {
          type: 'BadRequest',
          message: 'Bad request',
          code: 400,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };

      expect(() => {
        throw new BackendError('Test error', errorData);
      }).toThrow(BackendError);
    });

    it('should be catchable with try-catch', () => {
      const errorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Error',
        status: 400,
        error: {
          type: 'BadRequest',
          message: 'Bad request',
          code: 400,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };

      try {
        throw new BackendError('Test error', errorData);
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(BackendError);
        // eslint-disable-next-line
        expect((error as BackendError).message).toBe('Test error');
      }
    });
  });
});
