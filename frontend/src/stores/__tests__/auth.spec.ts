import { describe, expect, it, beforeEach } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useAuthStore } from '../auth';

describe('useAuthStore', () => {
  beforeEach(() => {
    localStorage.clear();
    setActivePinia(createPinia());
  });

  describe('initial state', () => {
    it('should initialize user as not authenticated', () => {
      const store = useAuthStore();

      expect(store.token).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });

    it('should initialize user as authenticated if token exists in localStorage', () => {
      localStorage.setItem('authToken', 'existing-token');

      const store = useAuthStore();

      expect(store.token).toBe('existing-token');
      expect(store.isAuthenticated).toBe(true);
    });
  });

  describe('setAuth', () => {
    it('should set user as authenticated by setting token', () => {
      const store = useAuthStore();

      store.setAuth('new-auth-token');

      expect(store.token).toBe('new-auth-token');
      expect(store.isAuthenticated).toBe(true);
      expect(localStorage.getItem('authToken')).toBe('new-auth-token');
    });
  });

  describe('clearAuth', () => {
    it('should set user as not authenticated by clearing token', () => {
      localStorage.setItem('authToken', 'existing-token');
      setActivePinia(createPinia());
      const store = useAuthStore();

      store.clearAuth();

      expect(store.token).toBeNull();
      expect(store.isAuthenticated).toBe(false);
      expect(localStorage.getItem('authToken')).toBeNull();
    });
  });
});
