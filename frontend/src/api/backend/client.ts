import { ApiError } from '@/api/backend/ApiError.ts';
import type { BackendErrorResponse, BackendSuccessResponse } from '@/api/backend/types.ts';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export async function backendClient<T>(path: string, options?: RequestInit): Promise<T> {
  let response: Response;

  try {
    response = await fetch(`${BASE_URL}${path}`, options);
  } catch (error: unknown) {
    throw ApiError.networkError(error instanceof Error ? error : undefined);
  }

  let data: unknown;

  try {
    data = await response.json();
  } catch (error: unknown) {
    throw ApiError.parseError(response.status, error instanceof Error ? error : undefined);
  }

  if (!response.ok) {
    throw ApiError.fromResponse(data as BackendErrorResponse);
  }

  return (data as BackendSuccessResponse<T>).data;
}
