export interface HttpClientInterface {
  request<T>(
    url: string,
    method: HttpMethod,
    data?: unknown,
    options?: HttpRequestOptions,
  ): Promise<T>;
}

export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

export interface HttpRequestOptions {
  headers?: Record<string, string>;
  baseUrl?: string;
}
