import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { backendClient } from '../client';
import { ApiError } from '@/api/ApiError';

describe('backendClient', () => {
  beforeEach(() => {
    vi.spyOn(global, 'fetch');
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('should return a successful response when response is ok', async () => {
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

    expect(result).toEqual({
      success: true,
      message: 'Request successful',
      status: 200,
      data: mockData,
    });
  });

  it('should return an unsuccessful response when response is not ok', async () => {
    const mockError = {
      type: 'NotFoundError',
      message: 'User not found',
      code: 404,
      timestamp: '2024-01-01T00:00:00Z',
    };
    const mockResponse = {
      success: false,
      message: 'Request failed',
      status: 404,
      error: mockError,
    };

    vi.mocked(fetch).mockResolvedValue({
      ok: false,
      json: () => Promise.resolve(mockResponse),
    } as Response);

    const result = await backendClient('/users/999');

    expect(result).toEqual({
      success: false,
      message: 'Request failed',
      status: 404,
      error: mockError,
    });
  });

  it('should throw an ApiError when there is a network error', async () => {
    vi.mocked(fetch).mockRejectedValue(new TypeError('Network Error'));

    await expect(backendClient('/users/1')).rejects.toThrow(ApiError);

    try {
      await backendClient('/users/1');
    } catch (error) {
      // eslint-disable-next-line
      expect(error).toBeInstanceOf(ApiError);
      // eslint-disable-next-line
      expect(error).toHaveProperty('message', 'An unexpected error occurred.');
      // eslint-disable-next-line
      expect(error).toHaveProperty('data');
    }
  });

  it('should throw an ApiError when there is a JSON parsing error', async () => {
    vi.mocked(fetch).mockResolvedValue({
      ok: true,
      json: () => Promise.reject(new SyntaxError('Unexpected token in JSON')),
    } as Response);

    await expect(backendClient('/users/1')).rejects.toThrow(ApiError);

    try {
      await backendClient('/users/1');
    } catch (error) {
      // eslint-disable-next-line
      expect(error).toBeInstanceOf(ApiError);
      // eslint-disable-next-line
      expect(error).toHaveProperty('message', 'An unexpected error occurred.');
      // eslint-disable-next-line
      expect(error).toHaveProperty('data');
    }
  });
});
