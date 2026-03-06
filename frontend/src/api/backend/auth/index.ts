import type { AuthToken, BackendResponse } from '@/api/backend/types.ts';
import { backendClient } from '@/api/backend/client.ts';

async function login(request: FormData): Promise<BackendResponse<AuthToken>> {
  return backendClient<AuthToken>('/auth/login', {
    method: 'POST',
    body: request,
  });
}

async function logout(): Promise<BackendResponse<[]>> {
  return backendClient<[]>('/auth/logout', {
    method: 'POST',
  });
}

async function register(request: FormData): Promise<BackendResponse<AuthToken>> {
  return backendClient<AuthToken>('/auth/register', {
    method: 'POST',
    body: request,
  });
}

export const authApi = {
  login,
  logout,
  register,
};
