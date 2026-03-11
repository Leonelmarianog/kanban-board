import { describe, it, expect, vi, beforeEach } from 'vitest';
import LoginView from '../LoginView.vue';
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

describe('LoginView', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should allow users to login', async () => {
    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'test@example.com',
      password: 'password123',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith({ name: 'Home' });
    });
    expect(localStorage.getItem('authToken')).toBe('test-token-123');
  });

  it('should display validation errors to users when attempting to login with invalid data', async () => {
    server.use(
      http.post('*/api/auth/login', () => {
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
                email: ['Invalid email or password.'],
              },
            },
          },
          { status: 422 },
        );
      }),
    );

    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'wrong@example.com',
      password: 'wrongpassword',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(wrapper.html()).toContain('Invalid email or password.');
    });
    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBeNull();
  });

  it('should display a toast to users when login fails due to invalid credentials', async () => {
    server.use(
      http.post('*/api/auth/login', () => {
        return HttpResponse.json(
          {
            success: false,
            message: 'Authentication failed',
            status: 401,
            error: {
              type: 'AuthenticationFailedException',
              message: 'Authentication failed',
              code: 401,
              timestamp: new Date().toISOString(),
            },
          },
          { status: 401 },
        );
      }),
    );

    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'test@example.com',
      password: 'wrongpassword',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(mockToastError).toHaveBeenCalledWith('Username or password incorrect.');
    });
    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBeNull();
  });

  it('should display a toast to users when login fails due to any unexpected errors', async () => {
    server.use(
      http.post('*/api/auth/login', () => {
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

    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'test@example.com',
      password: 'password123',
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
