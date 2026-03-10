import { describe, expect, it, vi, beforeEach } from 'vitest';
import App from '../App.vue';
import { server } from '@/../test/setup';
import { http, HttpResponse } from 'msw';
import { mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should show loading spinner when authenticated and data is pending', async () => {
    localStorage.setItem('authToken', 'test-token');

    // Delay the response to keep query pending
    server.use(
      http.get('*/api/v1/members/me', async () => {
        await new Promise((resolve) => setTimeout(resolve, 1000));
        return HttpResponse.json({
          success: true,
          message: 'Member retrieved successfully',
          status: 200,
          data: { id: 'user-123', full_name: 'Test User', email: 'test@example.com' },
        });
      }),
    );

    const wrapper = mountWithPlugins(App, {
      global: {
        stubs: {
          RouterView: {
            template: '<div data-testid="router-view">RouterView</div>',
          },
        },
      },
    });

    // Should show loading spinner while pending
    expect(wrapper.find('.animate-spin').exists()).toBe(true);
    expect(wrapper.find('[data-testid="router-view"]').exists()).toBe(false);
  });

  it('should show RouterView when not authenticated', async () => {
    // No auth token set
    const wrapper = mountWithPlugins(App, {
      global: {
        stubs: {
          RouterView: {
            template: '<div data-testid="router-view">RouterView</div>',
          },
        },
      },
    });

    // Should immediately show RouterView when not authenticated
    expect(wrapper.find('[data-testid="router-view"]').exists()).toBe(true);
    expect(wrapper.find('.animate-spin').exists()).toBe(false);
  });

  it('should show RouterView after successfully querying data', async () => {
    localStorage.setItem('authToken', 'test-token');

    const wrapper = mountWithPlugins(App, {
      global: {
        stubs: {
          RouterView: {
            template: '<div data-testid="router-view">RouterView</div>',
          },
        },
      },
    });

    // Wait for the query to complete
    await vi.waitFor(() => {
      expect(wrapper.find('[data-testid="router-view"]').exists()).toBe(true);
      expect(wrapper.find('.animate-spin').exists()).toBe(false);
    });
  });
});
