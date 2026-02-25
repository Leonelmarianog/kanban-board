import type { BackendErrorResponseInterface } from '@/api/backend/types.ts';

export class BackendError extends Error {
  public constructor(
    public readonly message: string,
    public readonly data: BackendErrorResponseInterface,
  ) {
    super(message);
    this.name = 'BackendError';
  }
}
