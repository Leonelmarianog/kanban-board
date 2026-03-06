import { describe, it, expect, vi, beforeEach } from 'vitest';
import HomeView from '../HomeView.vue';
import { mountWithPlugins } from '@/../test/helpers';

const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

// Mock BoardContainer to keep tests focused on HomeView
vi.mock('@/components/BoardContainer.vue', () => ({
  default: {
    name: 'BoardContainer',
    template: '<div data-testid="board-container">Board Content</div>',
  },
}));

describe('HomeView', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should render the page heading', () => {
    const wrapper = mountWithPlugins(HomeView);

    expect(wrapper.find('h1').text()).toBe('My board');
  });

  it('should render BoardContainer component', () => {
    const wrapper = mountWithPlugins(HomeView);

    expect(wrapper.find('[data-testid="board-container"]').exists()).toBe(true);
    expect(wrapper.find('[data-testid="board-container"]').text()).toBe('Board Content');
  });
});
