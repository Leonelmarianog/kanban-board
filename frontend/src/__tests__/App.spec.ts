import { describe, expect, it, vi, beforeEach } from 'vitest';
import App from '../App.vue';
import { server } from '@/../test/setup';
import { http, HttpResponse } from 'msw';
import { mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should clear auth and redirect to login when me query fails while authenticated', async () => {
    localStorage.setItem('authToken', 'test-token');

    server.use(
      http.get('*/api/v1/members/me', () => {
        return HttpResponse.json(
          {
            success: false,
            message: 'Unauthorized',
            status: 401,
            error: {
              type: 'AuthenticationError',
              message: 'Invalid token',
              code: 401,
              timestamp: new Date().toISOString(),
            },
          },
          { status: 401 },
        );
      }),
    );

    mountWithPlugins(App);

    await vi.waitFor(() => {
      expect(localStorage.getItem('authToken')).toBeNull();
      expect(mockPush).toHaveBeenCalledWith({ name: 'Login' });
    });
  });
});
