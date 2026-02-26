import type { HttpClientInterface, HttpMethod, HttpRequestOptions } from '@/http';
import type {
  BackendErrorResponseInterface,
  BackendSuccessResponseInterface,
} from '@/api/backend/types.ts';
import { HttpError } from '@/http';
import { BackendError } from '@/api/backend/BackendError.ts';

export class BackendClient {
  constructor(private readonly http: HttpClientInterface) {}

  async request<T>(
    url: string,
    method: HttpMethod,
    data?: unknown,
    options?: HttpRequestOptions,
  ): Promise<BackendSuccessResponseInterface<T>> {
    try {
      return await this.http.request<BackendSuccessResponseInterface<T>>(
        url,
        method,
        data,
        options,
      );
    } catch (error) {
      if (error instanceof HttpError) {
        throw new BackendError(error.message, error.data as BackendErrorResponseInterface);
      }

      throw error;
    }
  }
}
