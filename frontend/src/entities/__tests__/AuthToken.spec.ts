import { describe, it, expect } from 'vitest';
import { AuthToken } from '@/entities/AuthToken';
import type { AuthTokenInterface } from '@/entities/AuthTokenInterface';

describe('AuthToken', () => {
  describe('Constructor', () => {
    it('should create an instance with the provided token', () => {
      const token = 'my-auth-token-123';

      const authToken = new AuthToken(token);

      expect(authToken).toBeInstanceOf(AuthToken);
    });
  });

  describe('getToken', () => {
    it('should return the token value', () => {
      const token = 'my-auth-token-456';
      const authToken = new AuthToken(token);

      const result = authToken.getToken();

      expect(result).toBe(token);
    });
  });

  describe('create', () => {
    it('should create an AuthToken instance', () => {
      const data: AuthTokenInterface = { token: 'created-token-123' };

      const authToken = AuthToken.create(data);

      expect(authToken).toBeInstanceOf(AuthToken);
      expect(authToken.getToken()).toBe('created-token-123');
    });
  });
});
