import { describe, it, expect } from 'vitest';
import { MemberServiceError } from '@/services/backend/member/MemberServiceError';
import { BackendError } from '@/api/backend/BackendError';
import type { BackendErrorResponseInterface } from '@/api/backend/types';
import type { ErrorDataInterface } from '@/services/backend/types';

describe('MemberServiceError', () => {
  describe('Constructor', () => {
    it('should create an instance with message and data', () => {
      const errorData: ErrorDataInterface = {
        type: 'NotFoundError',
        message: 'Member not found.',
      };

      const error = new MemberServiceError('Member not found', errorData);

      expect(error).toBeInstanceOf(MemberServiceError);
      expect(error.message).toBe('Member not found');
      expect(error.data).toEqual(errorData);
    });

    it('should be an instance of Error', () => {
      const errorData: ErrorDataInterface = {
        type: 'AuthenticationError',
        message: 'Invalid token',
      };

      const error = new MemberServiceError('Auth error', errorData);

      expect(error).toBeInstanceOf(Error);
    });

    it('should have the correct name property', () => {
      const errorData: ErrorDataInterface = {
        type: 'SomeError',
        message: 'Some message',
      };

      const error = new MemberServiceError('Test error', errorData);

      expect(error.name).toBe('MemberServiceError');
    });
  });

  describe('fromBackendError', () => {
    it('should convert BackendError to MemberServiceError', () => {
      const backendErrorData: BackendErrorResponseInterface = {
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
      const backendError = new BackendError('Request failed', backendErrorData);

      const memberServiceError = MemberServiceError.fromBackendError(backendError);

      expect(memberServiceError).toBeInstanceOf(MemberServiceError);
      expect(memberServiceError.message).toBe('Unauthorized');
      expect(memberServiceError.data.type).toBe('AuthenticationError');
      expect(memberServiceError.data.message).toBe('Invalid token');
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
            email: ['The email format is invalid.'],
          },
        },
      };
      const backendError = new BackendError('Request failed', backendErrorData);

      const memberServiceError = MemberServiceError.fromBackendError(backendError);

      expect(memberServiceError.data.validationErrors).toEqual({
        email: ['The email format is invalid.'],
      });
    });

    it('should handle BackendError without validation_errors', () => {
      const backendErrorData: BackendErrorResponseInterface = {
        success: false,
        message: 'Not found',
        status: 404,
        error: {
          type: 'NotFoundError',
          message: 'Member not found',
          code: 404,
          timestamp: '2024-01-01T00:00:00Z',
        },
      };
      const backendError = new BackendError('Request failed', backendErrorData);

      const memberServiceError = MemberServiceError.fromBackendError(backendError);

      expect(memberServiceError.data.validationErrors).toBeUndefined();
    });
  });
});
