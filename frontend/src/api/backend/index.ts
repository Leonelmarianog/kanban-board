import { FetchHttpClient } from '@/http';
import { BackendClient } from './BackendClient';

export function createBackendClient(): BackendClient {
  const baseUrl = import.meta.env.VITE_API_BASE_URL;
  const httpClient = new FetchHttpClient(baseUrl);
  return new BackendClient(httpClient);
}

export const backendClient = createBackendClient();
export { BackendError } from './BackendError.ts';
export * from './types';
