import { describe, it, expect, vi, beforeEach } from 'vitest';
import PageLayout from '../PageLayout.vue';
import { server } from '@/../test/setup';
import { http, HttpResponse } from 'msw';
import { mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

const mockToast = vi.fn();
vi.mock('vue-toastification', () => ({
  useToast: () => ({
    error: mockToast,
  }),
}));

describe('PageLayout', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should render slot content', () => {
    const wrapper = mountWithPlugins(PageLayout, {
      slots: {
        default: '<div data-testid="content">Main content</div>',
        heading: '<h1>Page Title</h1>',
      },
    });

    expect(wrapper.find('[data-testid="content"]').exists()).toBe(true);
    expect(wrapper.find('h1').text()).toBe('Page Title');
  });

  it('should not show logout button when not authenticated', () => {
    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    expect(logoutButton.exists()).toBe(false);
  });

  it('should show logout button when authenticated', () => {
    localStorage.setItem('authToken', 'test-token');

    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    expect(logoutButton.exists()).toBe(true);
    expect(logoutButton.text()).toBe('Logout');
  });

  it('should logout successfully and navigate to login', async () => {
    localStorage.setItem('authToken', 'test-token');

    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    await logoutButton.trigger('click');

    await vi.waitFor(() => {
      expect(mockPush).toHaveBeenCalledWith({ name: 'Login' });
    });

    expect(localStorage.getItem('authToken')).toBeNull();
  });

  it('should show loading state during logout', async () => {
    localStorage.setItem('authToken', 'test-token');

    let resolveLogout: () => void;
    server.use(
      http.post('*/api/auth/logout', () => {
        return new Promise((resolve) => {
          resolveLogout = resolve;
        });
      }),
    );

    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    await logoutButton.trigger('click');

    await vi.waitFor(() => {
      expect(wrapper.find('button').text()).toBe('Logging out...');
    });

    resolveLogout!();
  });

  it('should cancel logout if request fails', async () => {
    localStorage.setItem('authToken', 'test-token');

    server.use(
      http.post('*/api/auth/logout', () => {
        return HttpResponse.json(
          { success: false, error: { message: 'Logout failed' } },
          { status: 500 },
        );
      }),
    );

    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    await logoutButton.trigger('click');

    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBe('test-token');
  });

  it('should notify user when logout fails', async () => {
    localStorage.setItem('authToken', 'test-token');

    server.use(
      http.post('*/api/auth/logout', () => {
        return HttpResponse.json(
          { success: false, error: { message: 'Logout failed' } },
          { status: 500 },
        );
      }),
    );

    const wrapper = mountWithPlugins(PageLayout);

    const logoutButton = wrapper.find('button');
    await logoutButton.trigger('click');

    await vi.waitFor(() => {
      expect(mockToast).toHaveBeenCalledWith(
        'An issue occurred while performing this action, please try again or contact support.',
      );
    });

    expect(mockPush).not.toHaveBeenCalled();
    expect(localStorage.getItem('authToken')).toBe('test-token');
  });
});
