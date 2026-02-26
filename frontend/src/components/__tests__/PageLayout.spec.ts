import { describe, it, expect, vi, beforeEach, type Mock } from 'vitest';
import { computed } from 'vue';
import { mount } from '@vue/test-utils';
import PageLayout from '@/components/PageLayout.vue';

vi.mock('@/composables/useLogout', () => ({
  useLogout: vi.fn(),
}));

vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(),
}));

import { useLogout } from '@/composables/useLogout';
import { useAuthStore } from '@/stores/auth';

describe('PageLayout.vue', () => {
  let mockLogout: Mock;

  beforeEach(() => {
    vi.clearAllMocks();
    mockLogout = vi.fn();

    vi.mocked(useLogout).mockReturnValue({
      logout: mockLogout,
      isLoading: computed(() => false),
      error: computed(() => null),
    });
  });

  it('renders slots', () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: false,
    } as unknown as ReturnType<typeof useAuthStore>);

    const wrapper = mount(PageLayout, {
      slots: {
        heading: '<h1>Title</h1>',
        default: '<p>Content</p>',
      },
    });

    expect(wrapper.find('header').text()).toBe('Title');
    expect(wrapper.find('main').text()).toBe('Content');
  });

  it('does not show logout button when not authenticated', () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: false,
    } as unknown as ReturnType<typeof useAuthStore>);

    const wrapper = mount(PageLayout);

    expect(wrapper.find('button').exists()).toBe(false);
  });

  it('shows logout button when authenticated', () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: true,
    } as unknown as ReturnType<typeof useAuthStore>);

    const wrapper = mount(PageLayout);

    expect(wrapper.find('button').exists()).toBe(true);
    expect(wrapper.find('button').text()).toBe('Logout');
  });

  it('shows loading text when logout is in progress', () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: true,
    } as unknown as ReturnType<typeof useAuthStore>);

    vi.mocked(useLogout).mockReturnValue({
      logout: mockLogout,
      isLoading: computed(() => true),
      error: computed(() => null),
    });

    const wrapper = mount(PageLayout);

    expect(wrapper.find('button').text()).toBe('Logging out...');
  });

  it('disables button when logout is in progress', () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: true,
    } as unknown as ReturnType<typeof useAuthStore>);

    vi.mocked(useLogout).mockReturnValue({
      logout: mockLogout,
      isLoading: computed(() => true),
      error: computed(() => null),
    });

    const wrapper = mount(PageLayout);

    expect(wrapper.find('button').element.hasAttribute('disabled')).toBe(true);
  });

  it('calls logout when button is clicked', async () => {
    vi.mocked(useAuthStore).mockReturnValue({
      isAuthenticated: true,
    } as unknown as ReturnType<typeof useAuthStore>);

    const wrapper = mount(PageLayout);

    await wrapper.find('button').trigger('click');

    expect(mockLogout).toHaveBeenCalled();
  });
});
