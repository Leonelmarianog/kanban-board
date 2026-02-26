import { describe, it, expect } from 'vitest';
import { LoginFormData } from '@/forms/LoginFormData';

describe('LoginFormData', () => {
  describe('Constructor', () => {
    it('should create an instance with all properties', () => {
      const formData = new LoginFormData('john@example.com', 'password123');

      expect(formData.email).toBe('john@example.com');
      expect(formData.password).toBe('password123');
    });
  });

  describe('toFormData', () => {
    it('should return FormData with all fields', () => {
      const loginForm = new LoginFormData('jane@example.com', 'securepassword');

      const formData = loginForm.toFormData();

      expect(formData).toBeInstanceOf(FormData);
      expect(formData.get('email')).toBe('jane@example.com');
      expect(formData.get('password')).toBe('securepassword');
    });
  });
});
