import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { backendClient } from '../client';
import { ApiError } from '@/api/backend/ApiError.ts';
import type { BackendErrorResponse } from '@/api/backend/types.ts';

describe('backendClient', () => {
  beforeEach(() => {
    vi.spyOn(global, 'fetch'); // Enable assertion on fetch.
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('should return data on successful response', async () => {
    const mockData = { id: '1', name: 'Test User' };
    const mockResponse = {
      success: true,
      message: 'Request successful',
      status: 200,
      data: mockData,
    };

    vi.mocked(fetch).mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(mockResponse),
    } as Response);

    const result = await backendClient<{ id: string; name: string }>('/users/1');

    expect(result).toEqual(mockData);
  });

  it('should throw ApiError for network errors', async () => {
    vi.mocked(fetch).mockRejectedValue(new TypeError('Network error'));

    const error = (await backendClient('/users').catch((e) => e)) as ApiError;

    expect(error).toBeInstanceOf(ApiError);
  });

  it('should throw ApiError for JSON parsing errors', async () => {
    vi.mocked(fetch).mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.reject(new SyntaxError('Unexpected token')),
    } as Response);

    const error = (await backendClient('/users').catch((e) => e)) as ApiError;

    expect(error).toBeInstanceOf(ApiError);
  });

  it('should throw ApiError on unsuccessful response', async () => {
    const mockResponse = {
      success: false,
      message: 'Request failed',
      status: 401,
      error: {
        type: 'AuthenticationException',
        message: 'Invalid token',
        code: 0,
        timestamp: '2024-01-01T00:00:00Z',
      },
    } as BackendErrorResponse;

    vi.mocked(fetch).mockResolvedValue({
      ok: false,
      status: 401,
      json: () => Promise.resolve(mockResponse),
    } as Response);

    const error = (await backendClient('/user/1').catch((e) => e)) as ApiError;

    expect(error).toBeInstanceOf(ApiError);
  });
});
