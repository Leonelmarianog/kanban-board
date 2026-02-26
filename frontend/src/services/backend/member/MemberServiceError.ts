import type { BackendError } from '@/api/backend';
import type { ErrorDataInterface } from '@/services/backend/types.ts';

export class MemberServiceError extends Error {
  public constructor(
    public readonly message: string,
    public readonly data: ErrorDataInterface,
  ) {
    super(message);
    this.name = 'MemberServiceError';
  }

  public static fromBackendError(backendError: BackendError): MemberServiceError {
    const { type, message, validation_errors } = backendError.data.error;

    return new MemberServiceError(backendError.data.message, {
      type,
      message,
      validationErrors: validation_errors,
    });
  }
}
