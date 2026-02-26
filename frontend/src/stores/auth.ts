import { defineStore } from 'pinia';
import type { Member } from '@/entities/Member.ts';

interface AuthState {
  token: string | null;
  isAuthenticated: boolean;
  member: Member | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('authToken'),
    isAuthenticated: !!localStorage.getItem('authToken'),
    member: null,
  }),

  actions: {
    setAuth(token: string) {
      this.token = token;
      this.isAuthenticated = true;
      localStorage.setItem('authToken', token);
    },

    setMember(member: Member) {
      this.member = member;
    },

    clearAuth() {
      this.token = null;
      this.isAuthenticated = false;
      this.member = null;
      localStorage.removeItem('authToken');
    },
  },
});
