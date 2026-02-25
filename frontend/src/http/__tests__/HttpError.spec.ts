import { describe, it, expect } from 'vitest';
import { HttpError } from '@/http';

describe('HttpError', () => {
  describe('Constructor', () => {
    it('should create an error with status and data', () => {
      const errorData = { message: 'Not found' };
      const error = new HttpError(404, errorData);

      expect(error.status).toBe(404);
      expect(error.data).toBe(errorData);
    });

    it('should set the error name to "HttpError"', () => {
      const error = new HttpError(500, {});

      expect(error.name).toBe('HttpError');
    });

    it('should set a default message', () => {
      const error = new HttpError(400, { error: 'Bad Request' });

      expect(error.message).toBe('There was an error with the request.');
    });

    it('should be an instance of Error', () => {
      const error = new HttpError(401, {});

      expect(error).toBeInstanceOf(Error);
    });
  });

  describe('Data types', () => {
    it('should accept object data', () => {
      const errorData = { errors: ['Field is required'] };
      const error = new HttpError(422, errorData);

      expect(error.data).toEqual(errorData);
    });

    it('should accept string data', () => {
      const error = new HttpError(500, 'Internal Server Error');

      expect(error.data).toBe('Internal Server Error');
    });

    it('should accept array data', () => {
      const errorData = ['Error 1', 'Error 2'];
      const error = new HttpError(400, errorData);

      expect(error.data).toEqual(errorData);
    });

    it('should accept null data', () => {
      const error = new HttpError(404, null);

      expect(error.data).toBeNull();
    });
  });
});
