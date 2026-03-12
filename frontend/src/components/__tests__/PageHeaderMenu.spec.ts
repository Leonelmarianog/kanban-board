import { describe, it, expect, vi, beforeEach } from 'vitest';
import PageHeaderMenu from '../PageHeaderMenu.vue';
import { server } from '@/../test/setup';
import { http, HttpResponse } from 'msw';
import { mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

vi.mock('vue-toastification', () => ({
  useToast: () => ({
    error: vi.fn(),
  }),
}));

describe('PageHeaderMenu', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    localStorage.setItem('authToken', 'test-token');
  });

  describe('button rendering', () => {
    it('should render button with avatar if set', async () => {
      server.use(
        http.get('*/api/v1/members/me', () => {
          return HttpResponse.json({
            success: true,
            message: 'Member retrieved successfully',
            status: 200,
            data: [
              {
                id: 'user-123',
                full_name: 'John Doe',
                initials: 'JD',
                avatar_url: 'https://example.com/avatar.jpg',
              },
            ],
          });
        }),
      );

      const wrapper = mountWithPlugins(PageHeaderMenu);

      await vi.waitFor(() => {
        const img = wrapper.find('[data-test="page-header-menu.avatar-image"]');
        expect(img.exists()).toBe(true);
        expect(img.attributes('src')).toBe('https://example.com/avatar.jpg');
        expect(img.attributes('alt')).toBe('John Doe');
      });
    });

    it('should render button with initials if avatar not set', async () => {
      server.use(
        http.get('*/api/v1/members/me', () => {
          return HttpResponse.json({
            success: true,
            message: 'Member retrieved successfully',
            status: 200,
            data: [
              {
                id: 'user-123',
                full_name: 'John Doe',
                initials: 'JD',
                avatar_url: null,
              },
            ],
          });
        }),
      );

      const wrapper = mountWithPlugins(PageHeaderMenu);

      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-image"]').exists()).toBe(false);
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('JD');
      });
    });
  });

  describe('dropdown menu', () => {
    it('should toggle dropdown menu on button click', async () => {
      const wrapper = mountWithPlugins(PageHeaderMenu);

      // Wait for data to load
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('TU');
      });

      // Dropdown should be closed initially
      expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(false);

      // Click the button to open the dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');
      expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(true);

      // Click the button again to close the dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');
      expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(false);
    });

    it('should close dropdown when clicking outside', async () => {
      const wrapper = mountWithPlugins(PageHeaderMenu);

      // Wait for data to load
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('TU');
      });

      // Open dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');
      expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(true);

      // Click outside the dropdown
      document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(false);
      });
    });

    it('should render dropdown menu correctly', async () => {
      const wrapper = mountWithPlugins(PageHeaderMenu);

      // Wait for data to load
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('TU');
      });

      // Open dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');

      // Check dropdown content
      expect(wrapper.find('[data-test="page-header-menu.dropdown"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="page-header-menu.user-name"]').text()).toBe('Test User');
      expect(wrapper.find('[data-test="page-header-menu.logout-button"]').text()).toBe('Logout');
    });
  });

  describe('logout', () => {
    it('should logout user when logout button is clicked', async () => {
      const wrapper = mountWithPlugins(PageHeaderMenu);

      // Wait for member data to load
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('TU');
      });

      // Open dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');

      // Click the logout button
      await wrapper.find('[data-test="page-header-menu.logout-button"]').trigger('click');

      await vi.waitFor(() => {
        expect(mockPush).toHaveBeenCalledWith({ name: 'Login' });
      });
    });

    it('should show loading state during logout', async () => {
      let resolveLogout!: () => void;
      server.use(
        http.post('*/api/auth/logout', async () => {
          await new Promise((resolve) => {
            resolveLogout = () => resolve(undefined);
          });

          return HttpResponse.json({
            success: true,
            message: 'Logout successful',
            status: 200,
            data: [],
          });
        }),
      );

      const wrapper = mountWithPlugins(PageHeaderMenu);

      // Wait for member data to load
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.avatar-initials"]').text()).toBe('TU');
      });

      // Open dropdown
      await wrapper.find('[data-test="page-header-menu.avatar-button"]').trigger('click');

      const logoutButton = wrapper.find('[data-test="page-header-menu.logout-button"]');

      // Initially shows "Logout"
      expect(logoutButton.text()).toBe('Logout');
      expect(logoutButton.attributes('disabled')).toBeUndefined();

      // Trigger logout
      await logoutButton.trigger('click');

      // During the logout process shows "Logging out..."
      await vi.waitFor(() => {
        expect(wrapper.find('[data-test="page-header-menu.logout-button"]').text()).toBe(
          'Logging out...',
        );
        expect(
          wrapper.find('[data-test="page-header-menu.logout-button"]').attributes('disabled'),
        ).toBeDefined();
      });

      // Resolve the logout to clean up
      resolveLogout();
    });
  });
});
