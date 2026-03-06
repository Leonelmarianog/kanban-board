import { defineStore } from 'pinia';

interface AuthState {
  token: string | null;
  isAuthenticated: boolean;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('authToken'),
    isAuthenticated: !!localStorage.getItem('authToken'),
  }),

  actions: {
    setAuth(token: string) {
      this.token = token;
      this.isAuthenticated = true;
      localStorage.setItem('authToken', token);
    },

    clearAuth() {
      this.token = null;
      this.isAuthenticated = false;
      localStorage.removeItem('authToken');
    },
  },
});
