export interface ErrorDataInterface {
  type: string;
  message: string;
  validationErrors?: Record<string, string[]>;
}

export interface BaseRequestInterface {
  toFormData(): FormData;
}
