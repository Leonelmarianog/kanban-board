import { describe, it, expect, vi, beforeEach } from 'vitest';
import RegisterView from '../RegisterView.vue';
import { server } from '@/../test/setup';
import { http, HttpResponse } from 'msw';
import { fillForm, submitForm, mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

const mockToastError = vi.fn();
vi.mock('vue-toastification', () => ({
  useToast: () => ({
    error: mockToastError,
  }),
}));

describe('RegisterView', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should allow users to register and automatically log in', async () => {
    const wrapper = mountWithPlugins(RegisterView);

    await fillForm(wrapper, {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith({ name: 'Home' });
    });
    expect(localStorage.getItem('authToken')).toBe('test-token-123');
  });

  it('should display validation errors to users when attempting registration with invalid data', async () => {
    server.use(
      http.post('*/api/auth/register', () => {
        return HttpResponse.json(
          {
            success: false,
            message: 'Validation failed',
            status: 422,
            error: {
              type: 'ValidationException',
              message: 'Validation failed',
              code: 422,
              timestamp: new Date().toISOString(),
              validation_errors: {
                email: ['An account with this email already exists.'],
              },
            },
          },
          { status: 422 },
        );
      }),
    );

    const wrapper = mountWithPlugins(RegisterView);

    await fillForm(wrapper, {
      first_name: 'John',
      last_name: 'Doe',
      email: 'duplicate@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(wrapper.html()).toContain('An account with this email already exists.');
    });
    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBeNull();
  });

  it('should display a toast to users when registration fails due to any unexpected errors', async () => {
    server.use(
      http.post('*/api/auth/register', () => {
        return HttpResponse.json(
          {
            success: false,
            message: 'Internal server error',
            status: 500,
            error: {
              type: 'InternalServerError',
              message: 'Internal server error',
              code: 500,
              timestamp: new Date().toISOString(),
            },
          },
          { status: 500 },
        );
      }),
    );

    const wrapper = mountWithPlugins(RegisterView);

    await fillForm(wrapper, {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith(
        'An issue occurred while performing this action, please try again or contact support.',
      );
    });
    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBeNull();
  });
});
