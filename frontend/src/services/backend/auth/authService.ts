import { AuthToken } from '@/entities/AuthToken.ts';
import type { AuthTokenInterface } from '@/entities/AuthTokenInterface.ts';
import { backendClient, BackendError } from '@/api/backend';
import { AuthServiceError } from '@/services/backend/auth/AuthServiceError.ts';
import type {
  LoginRequestInterface,
  RegisterRequestInterface,
} from '@/services/backend/auth/types.ts';

const register = async (data: RegisterRequestInterface): Promise<AuthToken> => {
  try {
    const { data: tokenJson } = await backendClient.request<AuthTokenInterface>(
      '/auth/register',
      'POST',
      data.toFormData(),
    );

    return AuthToken.create(tokenJson);
  } catch (error) {
    if (error instanceof BackendError) {
      throw AuthServiceError.fromBackendError(error);
    }

    throw error;
  }
};

const login = async (data: LoginRequestInterface): Promise<AuthToken> => {
  try {
    const { data: tokenJson } = await backendClient.request<AuthTokenInterface>(
      '/auth/login',
      'POST',
      data.toFormData(),
    );

    return AuthToken.create(tokenJson);
  } catch (error) {
    if (error instanceof BackendError) {
      throw AuthServiceError.fromBackendError(error);
    }

    throw error;
  }
};

export const authService = {
  register,
  login,
};
