import { describe, it, expect } from 'vitest';
import { RegisterFormData } from '@/forms/RegisterFormData';

describe('RegisterFormData', () => {
  describe('Constructor', () => {
    it('should create an instance with all properties', () => {
      const formData = new RegisterFormData(
        'John',
        'Doe',
        'john@example.com',
        'password123',
        'password123',
      );

      expect(formData.first_name).toBe('John');
      expect(formData.last_name).toBe('Doe');
      expect(formData.email).toBe('john@example.com');
      expect(formData.password).toBe('password123');
      expect(formData.password_confirmation).toBe('password123');
    });
  });

  describe('toFormData', () => {
    it('should return FormData with all fields', () => {
      const registerForm = new RegisterFormData(
        'Jane',
        'Smith',
        'jane@example.com',
        'securepassword',
        'securepassword',
      );

      const formData = registerForm.toFormData();

      expect(formData).toBeInstanceOf(FormData);
      expect(formData.get('first_name')).toBe('Jane');
      expect(formData.get('last_name')).toBe('Smith');
      expect(formData.get('email')).toBe('jane@example.com');
      expect(formData.get('password')).toBe('securepassword');
      expect(formData.get('password_confirmation')).toBe('securepassword');
    });
  });
});
