export interface BackendSuccessResponseInterface<T> {
  success: true;
  message: string;
  status: number;
  data: T;
}

export interface BackendErrorResponseInterface {
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

export interface MemberJsonInterface {
  id: string;
  full_name: string;
  initials: string;
  email: string;
  avatar_url?: string;
  bio?: string;
}
