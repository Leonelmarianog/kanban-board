import type { BaseRequestInterface } from '@/services/backend/types.ts';

export interface RegisterRequestInterface extends BaseRequestInterface {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
}
