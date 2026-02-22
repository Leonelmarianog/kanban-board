import type { ApiResponseInterface } from '@/api/backend/types.ts';
import type { AuthTokenInterface } from '@/entities/AuthTokenInterface.ts';

export interface RegisterRequestInterface {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface RegisterResponseInterface extends ApiResponseInterface<AuthTokenInterface> {} // eslint-disable-line
