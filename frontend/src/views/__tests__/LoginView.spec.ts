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

describe('LoginView', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should render the login form', () => {
    const wrapper = mountWithPlugins(LoginView);

    expect(wrapper.find('h2').text()).toContain('Sign in to continue');
    expect(wrapper.findAll('input')).toHaveLength(2);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Login');
  });

  it('should enable submit button when form is valid', async () => {
    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'test@example.com',
      password: 'password123',
    });

    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.attributes('disabled')).toBeUndefined();
  });

  it('should login successfully and navigate to home', async () => {
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

  it('should display validation errors from backend', async () => {
    server.use(
      http.post('*/api/auth/login', () => {
        return HttpResponse.json(
          {
            success: false,
            message: 'Validation failed',
            status: 422,
            error: {
              type: 'ValidationError',
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

  it('should show loading spinner during login', async () => {
    let resolveLogin: () => void;
    server.use(
      http.post('*/api/auth/login', () => {
        return new Promise((resolve) => {
          resolveLogin = resolve;
        });
      }),
    );

    const wrapper = mountWithPlugins(LoginView);

    await fillForm(wrapper, {
      email: 'test@example.com',
      password: 'password123',
    });

    await submitForm(wrapper);

    await vi.waitFor(() => {
      expect(wrapper.find('svg.animate-spin').exists()).toBe(true);
    });

    resolveLogin!();
  });
});
