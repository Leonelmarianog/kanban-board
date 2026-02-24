import type { HttpClientInterface, HttpMethod, HttpRequestOptions } from './types';

export class FetchHttpClient implements HttpClientInterface {
  constructor(private readonly baseUrl: string) {}

  async request<T>(
    url: string,
    method: HttpMethod,
    data?: unknown,
    options?: HttpRequestOptions,
  ): Promise<T> {
    const fullUrl = `${options?.baseUrl ?? this.baseUrl}${url}`;

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      ...options?.headers,
    };

    const response = await fetch(fullUrl, {
      method,
      headers,
      body: data ? JSON.stringify(data) : undefined,
    });

    if (!response.ok) {
      const errorBody = await response.json().catch(() => ({}));
      throw {
        status: response.status,
        data: errorBody,
      };
    }

    return response.json();
  }
}
