import type { HttpClientInterface, HttpMethod, HttpRequestOptions } from '@/http/types.ts';
import { HttpError } from '@/http/HttpError.ts';

export class FetchHttpClient implements HttpClientInterface {
  constructor(private readonly baseUrl: string) {}

  public async request<T>(
    url: string,
    method: HttpMethod,
    data?: unknown,
    options?: HttpRequestOptions,
  ): Promise<T> {
    const fullUrl = `${options?.baseUrl ?? this.baseUrl}${url}`;

    const response = await fetch(fullUrl, {
      method,
      headers: options?.headers,
      body: this.parseData(data),
    });

    if (!response.ok) {
      const errorResponse = await response.json();
      throw new HttpError(response.status, errorResponse);
    }

    return response.json();
  }

  private parseData(data: unknown): string | FormData | null {
    if (data instanceof FormData) {
      return data;
    }

    if (typeof data === 'object' && data !== null) {
      return JSON.stringify(data);
    }

    return null;
  }
}
