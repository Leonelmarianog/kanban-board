import { ApiError } from '@/api/ApiError.ts';
import type { BackendResponse } from '@/api/backend/types.ts';

const BASE_URL = import.meta.env.VITE_API_BASE_URL;

export async function backendClient<T>(
  path: string,
  options?: RequestInit,
): Promise<BackendResponse<T>> {
  try {
    const response = await fetch(`${BASE_URL}${path}`, options);

    const data = await response.json();

    if (!response.ok) {
      return {
        success: false,
        message: data.message,
        status: data.status,
        error: data.error,
      };
    }

    return {
      success: true,
      message: data.message,
      status: data.status,
      data: data.data,
    };
  } catch (error) {
    throw new ApiError('An unexpected error occurred.', { error });
  }
}
