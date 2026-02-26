import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { computed } from 'vue';
import LoginView from '@/views/LoginView.vue';
import { LoginFormData } from '@/forms/LoginFormData';

vi.mock('@/composables/useLogin', () => ({
  useLogin: vi.fn(),
}));

vi.mock('@/components/LoginForm.vue', () => ({
  default: {
    name: 'LoginForm',
    template: '<form data-testid="login-form"><slot /></form>',
    props: ['isLoading', 'errors'],
    emits: ['save'],
  },
}));

import { useLogin } from '@/composables/useLogin';
import type { AuthServiceError } from '@/services/backend/auth';

describe('LoginView.vue', () => {
  const mockLogin = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(useLogin).mockReturnValue({
      login: mockLogin,
      isLoading: computed(() => false),
      error: computed(() => null),
    } as unknown as ReturnType<typeof useLogin>);
  });

  it('renders LoginForm component', () => {
    const wrapper = mount(LoginView);

    expect(wrapper.find('[data-testid="login-form"]').exists()).toBe(true);
  });

  it('passes isLoading prop to LoginForm', () => {
    vi.mocked(useLogin).mockReturnValue({
      login: mockLogin,
      isLoading: computed(() => true),
      error: computed(() => null),
    } as unknown as ReturnType<typeof useLogin>);

    const wrapper = mount(LoginView);
    const loginForm = wrapper.findComponent({ name: 'LoginForm' });

    expect(loginForm.props('isLoading')).toBe(true);
  });

  it('calls login when LoginForm emits save', async () => {
    const wrapper = mount(LoginView);
    const formData = new LoginFormData('john@example.com', 'password');

    const loginForm = wrapper.findComponent({ name: 'LoginForm' });
    await loginForm.vm.$emit('save', formData);

    expect(mockLogin).toHaveBeenCalledWith(formData);
  });

  it('passes validation errors to LoginForm', () => {
    const mockError = {
      name: 'AuthServiceError',
      message: 'Validation failed',
      data: {
        type: 'ValidationError',
        message: 'Invalid data',
        validationErrors: {
          email: ['Email is required'],
        },
      },
    } as AuthServiceError;

    vi.mocked(useLogin).mockReturnValue({
      login: mockLogin,
      isLoading: computed(() => false),
      error: computed(() => mockError),
    } as unknown as ReturnType<typeof useLogin>);

    const wrapper = mount(LoginView);
    const loginForm = wrapper.findComponent({ name: 'LoginForm' });

    expect(loginForm.props('errors')).toEqual({
      email: ['Email is required'],
    });
  });
});
