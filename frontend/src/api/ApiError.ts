export class ApiError extends Error {
  public constructor(
    public readonly message: string,
    public readonly data: object,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}
