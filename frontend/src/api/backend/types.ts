export interface BackendSuccessResponse<T> {
  success: true;
  message: string;
  status: number;
  data: T;
}

export interface BackendErrorResponse {
  success: false;
  message: string;
  status: number;
  error: {
    type: string;
    message: string;
    code: number;
    timestamp: string;
    validation_errors?: Record<string, string[]>;
  };
}

export interface AuthToken {
  token: string;
}

export interface Member {
  id: string;
  full_name: string;
  initials: string;
  email: string;
  avatar_url?: string;
  bio?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
}
