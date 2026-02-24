export interface BaseApiResponseInterface {
  success: boolean;
  message: string;
  status: number;
}

export interface ApiResponseInterface<T> extends BaseApiResponseInterface {
  data: T;
}

export interface ErrorApiResponseInterface extends BaseApiResponseInterface {
  errors: {
    type: string;
    message: string;
    code: number;
    timestamp: string;
    validation_errors: Record<string, string[]>;
  };
}
