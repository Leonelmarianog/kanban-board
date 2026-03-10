import { describe, it, expect } from 'vitest';
import { ApiError, ApiErrorType } from '../ApiError';
import type { BackendErrorResponse } from '../types';

describe('fromResponse', () => {
  it('should map all properties from a full response', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };

    const error = ApiError.fromResponse(response);

    expect(error.message).toBe('Invalid token');
    expect(error.type).toBe(ApiErrorType.AuthenticationException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(401);
    expect(error.timestamp).toBe('2024-01-01T00:00:00Z');
    expect(error.validationErrors).toBeUndefined();
    expect(error.cause).toBeUndefined();
  });

  it('should use defaults when error object is missing', () => {
    const response = {
      success: false,
      message: 'Request failed',
      status: 500,
    } as unknown as BackendErrorResponse;

    const error = ApiError.fromResponse(response);

    expect(error.message).toBe('An error occurred');
    expect(error.type).toBe(ApiErrorType.UnexpectedException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(500);
    expect(error.timestamp).toEqual(expect.any(String));
  });

  it('should use default message when error.message is missing', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 500,
      error: {
        type: 'UnexpectedException',
        code: 500,
        timestamp: '2024-01-01T00:00:00Z',
      },
    } as unknown as BackendErrorResponse;

    const error = ApiError.fromResponse(response);

    expect(error.message).toBe('An error occurred');
  });

  it('should use default code when error.code is missing', () => {
    const response = {
      success: false,
      message: 'Request failed',
      status: 500,
      error: {
        type: 'UnexpectedException',
        message: 'Something went wrong',
        timestamp: '2024-01-01T00:00:00Z',
      },
    } as unknown as BackendErrorResponse;

    const error = ApiError.fromResponse(response);

    expect(error.code).toBe(0);
  });

  it('should use current timestamp when error.timestamp is missing', () => {
    const response = {
      success: false,
      message: 'Request failed',
      status: 500,
      error: {
        type: 'UnexpectedException',
        message: 'Something went wrong',
        code: 500,
      },
    } as unknown as BackendErrorResponse;

    const error = ApiError.fromResponse(response);

    expect(error.timestamp).toEqual(expect.any(String));
  });

  it('should normalize unknown error type to UnexpectedException', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 500,
      error: {
        type: 'SomeUnknownType',
        message: 'Unknown error',
        code: 500,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };

    const error = ApiError.fromResponse(response);

    expect(error.type).toBe(ApiErrorType.UnexpectedException);
  });

  it('should preserve valid error type', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 422,
      error: {
        type: 'ValidationException',
        message: 'Validation failed',
        code: 422,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };

    const error = ApiError.fromResponse(response);

    expect(error.type).toBe(ApiErrorType.ValidationException);
  });

  it('should include validation_errors when present', () => {
    const validationErrors = {
      email: ['Email is required'],
      password: ['Password must be at least 8 characters'],
    };
    const response: BackendErrorResponse = {
      success: false,
      message: 'Validation failed',
      status: 422,
      error: {
        type: 'ValidationException',
        message: 'Validation failed',
        code: 422,
        timestamp: '2024-01-01T00:00:00Z',
        validation_errors: validationErrors,
      },
    };

    const error = ApiError.fromResponse(response);

    expect(error.validationErrors).toEqual(validationErrors);
  });

  it('should have undefined validationErrors when not present', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };

    const error = ApiError.fromResponse(response);

    expect(error.validationErrors).toBeUndefined();
  });
});

describe('networkError', () => {
  it('should create error with cause when provided', () => {
    const cause = new TypeError('Failed to fetch');

    const error = ApiError.networkError(cause);

    expect(error.message).toBe('Network error. Please check your connection.');
    expect(error.type).toBe(ApiErrorType.UnexpectedException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(0);
    expect(error.cause).toBe(cause);
    expect(error.validationErrors).toBeUndefined();
  });

  it('should create error without cause when not provided', () => {
    const error = ApiError.networkError();

    expect(error.message).toBe('Network error. Please check your connection.');
    expect(error.type).toBe(ApiErrorType.UnexpectedException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(0);
    expect(error.cause).toBeUndefined();
  });
});

describe('parseError', () => {
  it('should create error with cause when provided', () => {
    const cause = new SyntaxError('Unexpected token');

    const error = ApiError.parseError(200, cause);

    expect(error.message).toBe('Failed to parse server response.');
    expect(error.type).toBe(ApiErrorType.UnexpectedException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(200);
    expect(error.cause).toBe(cause);
    expect(error.validationErrors).toBeUndefined();
  });

  it('should create error without cause when not provided', () => {
    const error = ApiError.parseError(500);

    expect(error.message).toBe('Failed to parse server response.');
    expect(error.type).toBe(ApiErrorType.UnexpectedException);
    expect(error.code).toBe(0);
    expect(error.status).toBe(500);
    expect(error.cause).toBeUndefined();
  });
});

describe('isApiError', () => {
  it('should return true for ApiError instance', () => {
    const error = ApiError.networkError();

    expect(ApiError.isApiError(error)).toBe(true);
  });

  it('should return false for Error instance', () => {
    const error = new Error('Some error');

    expect(ApiError.isApiError(error)).toBe(false);
  });

  it('should return false for plain object', () => {
    expect(ApiError.isApiError({})).toBe(false);
  });

  it('should return false for null', () => {
    expect(ApiError.isApiError(null)).toBe(false);
  });

  it('should return false for undefined', () => {
    expect(ApiError.isApiError(undefined)).toBe(false);
  });

  it('should return false for string', () => {
    expect(ApiError.isApiError('error')).toBe(false);
  });
});

describe('isValidationError', () => {
  it('should return true for ValidationException with validationErrors', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Validation failed',
      status: 422,
      error: {
        type: 'ValidationException',
        message: 'Validation failed',
        code: 422,
        timestamp: '2024-01-01T00:00:00Z',
        validation_errors: { email: ['Invalid email'] },
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isValidationError(error)).toBe(true);
  });

  it('should return false for ValidationException without validationErrors', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Validation failed',
      status: 422,
      error: {
        type: 'ValidationException',
        message: 'Validation failed',
        code: 422,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isValidationError(error)).toBe(false);
  });

  it('should return false for other ApiError type', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isValidationError(error)).toBe(false);
  });

  it('should return false for non-ApiError', () => {
    const error = new Error('Some error');

    expect(ApiError.isValidationError(error)).toBe(false);
  });
});

describe('isUnauthenticatedError', () => {
  it('should return true for AuthenticationException', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isUnauthenticatedError(error)).toBe(true);
  });

  it('should return false for other ApiError type', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Validation failed',
      status: 422,
      error: {
        type: 'ValidationException',
        message: 'Validation failed',
        code: 422,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isUnauthenticatedError(error)).toBe(false);
  });

  it('should return false for non-ApiError', () => {
    const error = new Error('Some error');

    expect(ApiError.isUnauthenticatedError(error)).toBe(false);
  });
});

describe('isInvalidCredentialsError', () => {
  it('should return true for AuthenticationFailedException', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationFailedException',
        message: 'Invalid credentials',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isInvalidCredentialsError(error)).toBe(true);
  });

  it('should return false for other ApiError type', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isInvalidCredentialsError(error)).toBe(false);
  });

  it('should return false for non-ApiError', () => {
    const error = new Error('Some error');

    expect(ApiError.isInvalidCredentialsError(error)).toBe(false);
  });
});

describe('isUnexpectedError', () => {
  it('should return true for UnexpectedException', () => {
    const error = ApiError.networkError();

    expect(ApiError.isUnexpectedError(error)).toBe(true);
  });

  it('should return false for other ApiError type', () => {
    const response: BackendErrorResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    };
    const error = ApiError.fromResponse(response);

    expect(ApiError.isUnexpectedError(error)).toBe(false);
  });

  it('should return false for non-ApiError', () => {
    const error = new Error('Some error');

    expect(ApiError.isUnexpectedError(error)).toBe(false);
  });
});
