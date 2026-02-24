import { FetchHttpClient, type HttpClientInterface } from '@/http';
import type { ErrorApiResponseInterface } from './types';

export interface BackendClientInterface extends HttpClientInterface {} // eslint-disable-line

export function createBackendClient(): BackendClientInterface {
  const baseUrl = import.meta.env.VITE_API_BASE_URL;
  return new FetchHttpClient(baseUrl);
}

export const backendClient = createBackendClient();

export { type ErrorApiResponseInterface };
export * from './types';
