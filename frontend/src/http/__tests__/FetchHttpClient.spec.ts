import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { FetchHttpClient } from '@/http';
import { HttpError } from '@/http';

describe('FetchHttpClient', () => {
  const baseUrl = 'https://api.example.com';
  let client: FetchHttpClient;

  beforeEach(() => {
    client = new FetchHttpClient(baseUrl);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('Constructor', () => {
    it('should create an instance with the provided base URL', () => {
      expect(client).toBeInstanceOf(FetchHttpClient);
    });
  });

  describe('request', () => {
    it('should make a GET request and return JSON response', async () => {
      const responseData = { id: 1, name: 'Test' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      } as Response);

      const result = await client.request('/users', 'GET');

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users', {
        method: 'GET',
        headers: undefined,
        body: null,
      });
      expect(result).toEqual(responseData);
    });

    it('should make a POST request with JSON body', async () => {
      const requestData = { name: 'New User' };
      const responseData = { id: 1, name: 'New User' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      } as Response);

      const result = await client.request('/users', 'POST', requestData);

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users', {
        method: 'POST',
        headers: undefined,
        body: JSON.stringify(requestData),
      });
      expect(result).toEqual(responseData);
    });

    it('should make a PUT request with JSON body', async () => {
      const requestData = { name: 'Updated User' };
      const responseData = { id: 1, name: 'Updated User' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      } as Response);

      const result = await client.request('/users/1', 'PUT', requestData);

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users/1', {
        method: 'PUT',
        headers: undefined,
        body: JSON.stringify(requestData),
      });
      expect(result).toEqual(responseData);
    });

    it('should make a PATCH request with JSON body', async () => {
      const requestData = { name: 'Patched User' };
      const responseData = { id: 1, name: 'Patched User' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      } as Response);

      const result = await client.request('/users/1', 'PATCH', requestData);

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users/1', {
        method: 'PATCH',
        headers: undefined,
        body: JSON.stringify(requestData),
      });
      expect(result).toEqual(responseData);
    });

    it('should make a DELETE request', async () => {
      const responseData = { success: true };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      } as Response);

      const result = await client.request('/users/1', 'DELETE');

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users/1', {
        method: 'DELETE',
        headers: undefined,
        body: null,
      });
      expect(result).toEqual(responseData);
    });

    it('should include custom headers in the request', async () => {
      const headers = { Authorization: 'Bearer token' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/users', 'GET', undefined, { headers });

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/users', {
        method: 'GET',
        headers,
        body: null,
      });
    });

    it('should override base URL with options.baseUrl', async () => {
      const customBaseUrl = 'https://custom.api.com';
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/users', 'GET', undefined, { baseUrl: customBaseUrl });

      expect(fetch).toHaveBeenCalledWith('https://custom.api.com/users', {
        method: 'GET',
        headers: undefined,
        body: null,
      });
    });

    it('should send FormData without stringifying', async () => {
      const formData = new FormData();
      formData.append('name', 'Test User');
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/upload', 'POST', formData);

      expect(fetch).toHaveBeenCalledWith('https://api.example.com/upload', {
        method: 'POST',
        headers: undefined,
        body: formData,
      });
    });

    it('should throw HttpError when response is not ok', async () => {
      const errorData = { message: 'Not found' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: false,
        status: 404,
        json: async () => errorData,
      } as Response);

      await expect(client.request('/users/999', 'GET')).rejects.toThrow(HttpError);
    });

    it('should include status and data in HttpError', async () => {
      const errorData = { message: 'Unauthorized' };
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: false,
        status: 401,
        json: async () => errorData,
      } as Response);

      try {
        await client.request('/protected', 'GET');
      } catch (error) {
        // eslint-disable-next-line
        expect(error).toBeInstanceOf(HttpError);
        // eslint-disable-next-line
        expect((error as HttpError).status).toBe(401);
        // eslint-disable-next-line
        expect((error as HttpError).data).toEqual(errorData);
      }
    });
  });

  describe('parseData (private method behavior)', () => {
    it('should return null for undefined data', async () => {
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/users', 'GET', undefined);

      expect(fetch).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: null,
        }),
      );
    });

    it('should return null for null data', async () => {
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/users', 'GET', null);

      expect(fetch).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: null,
        }),
      );
    });

    it('should return null for primitive values', async () => {
      vi.spyOn(global, 'fetch').mockResolvedValueOnce({
        ok: true,
        json: async () => ({}),
      } as Response);

      await client.request('/users', 'GET', 'string');

      expect(fetch).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          body: null,
        }),
      );
    });
  });
});
