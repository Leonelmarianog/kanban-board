import type { AuthTokenInterface } from '@/entities/AuthTokenInterface.ts';

export class AuthToken implements AuthTokenInterface {
  public constructor(private readonly token: string) {}

  public getToken() {
    return this.token;
  }

  static create(data: AuthTokenInterface): AuthToken {
    return new AuthToken(data.token);
  }
}
