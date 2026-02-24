import { backendClient } from '@/api/backend';
import type {
  RegisterRequestInterface,
  RegisterResponseInterface,
} from '@/services/backend/auth/types.ts';
import { AuthToken } from '@/entities/AuthToken.ts';

export const register = async (request: RegisterRequestInterface): Promise<AuthToken> => {
  const { data: tokenJson } = await backendClient.request<RegisterResponseInterface>(
    '/auth/register',
    'POST',
    request,
  );
  return AuthToken.create(tokenJson);
};

export const authService = {
  register,
};
