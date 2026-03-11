import { describe, expect, it, vi, beforeEach } from 'vitest';
import { routerBeforeEach } from '../guards';
import type { RouteLocationNormalized } from 'vue-router';

vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(),
}));

import { useAuthStore } from '@/stores/auth';

function createMockRoute(
  meta: { requiresGuest?: boolean; requiresAuth?: boolean } = {},
): RouteLocationNormalized {
  return {
    path: '/test',
    name: 'Test',
    params: {},
    query: {},
    hash: '',
    fullPath: '/test',
    matched: [],
    redirectedFrom: undefined,
    meta,
  } as RouteLocationNormalized;
}

describe('routerBeforeEach', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('Guest routes', () => {
    it('should redirect authenticated users to Home', () => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        token: 'test-token',
      } as ReturnType<typeof useAuthStore>);

      const to = createMockRoute({ requiresGuest: true });
      const result = routerBeforeEach(to);

      expect(result).toEqual({ name: 'Home' });
    });

    it('should allow unauthenticated users to proceed', () => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: false,
        token: null,
      } as ReturnType<typeof useAuthStore>);

      const to = createMockRoute({ requiresGuest: true });
      const result = routerBeforeEach(to);

      expect(result).toBeUndefined(); // result = undefined --> navigation proceeds
    });
  });

  describe('Protected routes', () => {
    it('should redirect unauthenticated users to Login', () => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: false,
        token: null,
      } as ReturnType<typeof useAuthStore>);

      const to = createMockRoute({ requiresAuth: true });
      const result = routerBeforeEach(to);

      expect(result).toEqual({ name: 'Login' });
    });

    it('should allow authenticated users to proceed', () => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        token: 'test-token',
      } as ReturnType<typeof useAuthStore>);

      const to = createMockRoute({ requiresAuth: true });
      const result = routerBeforeEach(to);

      expect(result).toBeUndefined(); // result = undefined --> navigation proceeds
    });
  });

  describe('Public routes', () => {
    it('should allow all users to proceed', () => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: false,
        token: null,
      } as ReturnType<typeof useAuthStore>);

      const to = createMockRoute({});
      const result = routerBeforeEach(to);

      expect(result).toBeUndefined(); // result = undefined --> navigation proceeds
    });
  });
});
