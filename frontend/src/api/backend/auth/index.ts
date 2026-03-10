import type { AuthToken } from '@/api/backend/types.ts';
import { backendClient } from '@/api/backend/client.ts';

async function login(request: FormData): Promise<AuthToken> {
  return backendClient<AuthToken>('/auth/login', {
    method: 'POST',
    body: request,
    headers: {
      Accept: 'application/json',
    },
  });
}

async function logout(): Promise<void> {
  return backendClient<void>('/auth/logout', {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(localStorage.getItem('authToken') && {
        Authorization: `Bearer ${localStorage.getItem('authToken')}`,
      }),
    },
  });
}

async function register(request: FormData): Promise<AuthToken> {
  return backendClient<AuthToken>('/auth/register', {
    method: 'POST',
    body: request,
    headers: {
      Accept: 'application/json',
    },
  });
}

export const authApi = {
  login,
  logout,
  register,
};
