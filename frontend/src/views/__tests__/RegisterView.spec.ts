import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { computed } from 'vue';
import RegisterView from '@/views/RegisterView.vue';
import { RegisterFormData } from '@/forms/RegisterFormData';

vi.mock('@/composables/useRegister', () => ({
  useRegister: vi.fn(),
}));

vi.mock('@/components/RegisterForm.vue', () => ({
  default: {
    name: 'RegisterForm',
    template: '<form data-testid="register-form"><slot /></form>',
    props: ['isLoading', 'errors'],
    emits: ['save'],
  },
}));

import { useRegister } from '@/composables/useRegister';
import type { AuthServiceError } from '@/services/backend/auth';

describe('RegisterView.vue', () => {
  const mockRegister = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(useRegister).mockReturnValue({
      register: mockRegister,
      isLoading: computed(() => false),
      error: computed(() => null),
    } as unknown as ReturnType<typeof useRegister>);
  });

  it('renders RegisterForm component', () => {
    const wrapper = mount(RegisterView);

    expect(wrapper.find('[data-testid="register-form"]').exists()).toBe(true);
  });

  it('passes isLoading prop to RegisterForm', () => {
    vi.mocked(useRegister).mockReturnValue({
      register: mockRegister,
      isLoading: computed(() => true),
      error: computed(() => null),
    } as unknown as ReturnType<typeof useRegister>);

    const wrapper = mount(RegisterView);
    const registerForm = wrapper.findComponent({ name: 'RegisterForm' });

    expect(registerForm.props('isLoading')).toBe(true);
  });

  it('calls register when RegisterForm emits save', async () => {
    const wrapper = mount(RegisterView);
    const formData = new RegisterFormData(
      'John',
      'Doe',
      'john@example.com',
      'password',
      'password',
    );

    const registerForm = wrapper.findComponent({ name: 'RegisterForm' });
    await registerForm.vm.$emit('save', formData);

    expect(mockRegister).toHaveBeenCalledWith(formData);
  });

  it('passes validation errors to RegisterForm', () => {
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

    vi.mocked(useRegister).mockReturnValue({
      register: mockRegister,
      isLoading: computed(() => false),
      error: computed(() => mockError),
    } as unknown as ReturnType<typeof useRegister>);

    const wrapper = mount(RegisterView);
    const registerForm = wrapper.findComponent({ name: 'RegisterForm' });

    expect(registerForm.props('errors')).toEqual({
      email: ['Email is required'],
    });
  });
});
