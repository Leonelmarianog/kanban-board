import { describe, it, expect } from 'vitest';
import { useValidationErrors } from '@/composables';

describe('useValidationErrors', () => {
  describe('initial state', () => {
    it('should initialize with empty errors', () => {
      const { errors, hasErrors } = useValidationErrors();

      expect(errors.value).toEqual({});
      expect(hasErrors()).toBe(false);
    });
  });

  describe('setErrors', () => {
    it('should set validation errors when shape is correct', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      const validationErrors = {
        email: ['Invalid email format'],
        password: ['Password is required', 'Password must be at least 8 characters'],
      };

      setErrors(validationErrors);

      expect(errors.value).toEqual(validationErrors);
      expect(hasErrors()).toBe(true);
    });

    it('should set empty object when provided', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);

      setErrors({});

      expect(errors.value).toEqual({});
      expect(hasErrors()).toBe(false);
    });

    it('should replace existing errors', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      setErrors({ password: ['Password is required'] });

      expect(errors.value).toEqual({ password: ['Password is required'] });
      expect(hasErrors()).toBe(true);
    });

    it('should do nothing when set to undefined', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);

      setErrors(undefined);

      // Should remain unchanged
      expect(errors.value).toEqual({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);
    });

    it('should do nothing when set to null', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);

      setErrors(null as unknown as Record<string, string[]>);

      // Should remain unchanged
      expect(errors.value).toEqual({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);
    });

    it('should do nothing when values are not string arrays', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);

      // Invalid: numbers instead of strings
      setErrors({ field: [1, 2, 3] as unknown as string[] });

      // Should remain unchanged
      expect(errors.value).toEqual({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);
    });

    it('should do nothing when value is not an array', () => {
      const { errors, setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);

      // Invalid: string instead of array
      setErrors({ field: 'error message' as unknown as string[] });

      // Should remain unchanged
      expect(errors.value).toEqual({ email: ['Invalid email'] });
      expect(hasErrors()).toBe(true);
    });
  });

  describe('clearErrors', () => {
    it('should clear all errors', () => {
      const { errors, setErrors, clearErrors, hasErrors } = useValidationErrors();

      setErrors({
        email: ['Invalid email'],
        password: ['Password is required'],
      });

      expect(hasErrors()).toBe(true);

      clearErrors();

      expect(errors.value).toEqual({});
      expect(hasErrors()).toBe(false);
    });
  });

  describe('hasErrors', () => {
    it('should return false when no errors exist', () => {
      const { hasErrors } = useValidationErrors();
      expect(hasErrors()).toBe(false);
    });

    it('should return true when errors exist', () => {
      const { setErrors, hasErrors } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });

      expect(hasErrors()).toBe(true);
    });
  });

  describe('getErrors', () => {
    it('should return errors for a specific field', () => {
      const { setErrors, getErrors } = useValidationErrors();

      setErrors({
        email: ['Invalid email format', 'Email is required'],
        password: ['Password is required'],
      });

      expect(getErrors('email')).toEqual(['Invalid email format', 'Email is required']);
      expect(getErrors('password')).toEqual(['Password is required']);
    });

    it('should return undefined for non-existent field', () => {
      const { getErrors } = useValidationErrors();

      expect(getErrors('nonexistent')).toBeUndefined();
    });
  });

  describe('hasFieldError', () => {
    it('should return true when field has errors', () => {
      const { setErrors, hasFieldError } = useValidationErrors();

      setErrors({ email: ['Invalid email'] });

      expect(hasFieldError('email')).toBe(true);
      expect(hasFieldError('password')).toBe(false);
    });

    it('should return false when no errors exist', () => {
      const { hasFieldError } = useValidationErrors();

      expect(hasFieldError('email')).toBe(false);
    });
  });

  describe('errors computed', () => {
    it('should return reactive errors object', () => {
      const { errors, setErrors, clearErrors } = useValidationErrors();

      expect(errors.value).toEqual({});

      setErrors({ email: ['Invalid email'] });
      expect(errors.value).toEqual({ email: ['Invalid email'] });

      clearErrors();
      expect(errors.value).toEqual({});
    });
  });
});
