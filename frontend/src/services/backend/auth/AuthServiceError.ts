import type { BackendError } from '@/api/backend';
import type { ErrorDataInterface } from '@/services/backend/types.ts';

export class AuthServiceError extends Error {
  public constructor(
    public readonly message: string,
    public readonly data: ErrorDataInterface,
  ) {
    super(message);
    this.name = 'AuthServiceError';
  }

  public static fromBackendError(backendError: BackendError): AuthServiceError {
    const { type, message, validation_errors } = backendError.data.error;

    return new AuthServiceError(backendError.data.message, {
      type,
      message,
      validationErrors: validation_errors,
    });
  }
}
