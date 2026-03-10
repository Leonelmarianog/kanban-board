import type { BackendErrorResponse } from '@/api/backend/types.ts';

export enum ApiErrorType {
  AuthenticationException = 'AuthenticationException',
  AuthenticationFailedException = 'AuthenticationFailedException',
  ValidationException = 'ValidationException',
  UnexpectedException = 'UnexpectedException',
}

export class ApiError extends Error {
  readonly name = 'ApiError';

  private constructor(
    message: string,
    public readonly type: ApiErrorType,
    public readonly code: number,
    public readonly status: number,
    public readonly timestamp: string,
    public readonly validationErrors?: Record<string, string[]>,
    public readonly cause?: Error,
  ) {
    super(message);
  }

  private static normalizeType(type: string): ApiErrorType {
    const validTypes = Object.values(ApiErrorType);
    return validTypes.includes(type as ApiErrorType)
      ? (type as ApiErrorType)
      : ApiErrorType.UnexpectedException;
  }

  static fromResponse(response: BackendErrorResponse): ApiError {
    const error = response.error ?? {};

    return new ApiError(
      error.message ?? 'An error occurred',
      this.normalizeType(error.type),
      error.code ?? 0,
      response.status,
      error.timestamp ?? new Date().toISOString(),
      error.validation_errors,
    );
  }

  static networkError(cause?: Error): ApiError {
    return new ApiError(
      'Network error. Please check your connection.',
      ApiErrorType.UnexpectedException,
      0,
      0,
      new Date().toISOString(),
      undefined,
      cause,
    );
  }

  static parseError(status: number, cause?: Error): ApiError {
    return new ApiError(
      'Failed to parse server response.',
      ApiErrorType.UnexpectedException,
      0,
      status,
      new Date().toISOString(),
      undefined,
      cause,
    );
  }

  static isApiError(error: unknown): error is ApiError {
    return error instanceof ApiError;
  }

  static isValidationError(error: unknown): error is ApiError {
    return (
      ApiError.isApiError(error) &&
      error.type === ApiErrorType.ValidationException &&
      !!error.validationErrors
    );
  }

  static isUnauthenticatedError(error: unknown): error is ApiError {
    return ApiError.isApiError(error) && error.type === ApiErrorType.AuthenticationException;
  }

  static isInvalidCredentialsError(error: unknown): error is ApiError {
    return ApiError.isApiError(error) && error.type === ApiErrorType.AuthenticationFailedException;
  }

  static isUnexpectedError(error: unknown): error is ApiError {
    return ApiError.isApiError(error) && error.type === ApiErrorType.UnexpectedException;
  }
}
