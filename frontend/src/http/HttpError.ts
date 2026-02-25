export class HttpError extends Error {
  public constructor(
    public readonly status: number,
    public readonly data: unknown,
  ) {
    super('There was an error with the request.');
    this.name = 'HttpError';
  }
}
