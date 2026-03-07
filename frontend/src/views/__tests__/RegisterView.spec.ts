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

  it('should render the registration form', () => {
    const wrapper = mountWithPlugins(RegisterView);

    expect(wrapper.find('h2').text()).toContain('Sign up to continue');
    expect(wrapper.findAll('input')).toHaveLength(5);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Register');
  });

  it('should enable submit button when form is valid', async () => {
    const wrapper = mountWithPlugins(RegisterView);

    await fillForm(wrapper, {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });

    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.attributes('disabled')).toBeUndefined();
  });

  it('should register successfully and navigate to home', async () => {
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

  it('should display validation errors from backend', async () => {
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

  it('should notify user when an unexpected error occurs during registration', async () => {
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

  it('should notify user on network error', async () => {
    server.use(
      http.post('*/api/auth/register', () => {
        return new Response(null, { status: 500 });
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
  });

  it('should show loading spinner during registration', async () => {
    let resolveRegister: () => void;
    server.use(
      http.post('*/api/auth/register', () => {
        return new Promise((resolve) => {
          resolveRegister = resolve;
        });
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
      expect(wrapper.find('svg.animate-spin').exists()).toBe(true);
    });

    resolveRegister!();
  });
});
