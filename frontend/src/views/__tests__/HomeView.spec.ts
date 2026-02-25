import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import HomeView from '@/views/HomeView.vue';

vi.mock('@/components/BoardContainer.vue', () => ({
  default: {
    name: 'BoardContainer',
    template: '<div data-testid="board-container"></div>',
  },
}));

describe('HomeView.vue', () => {
  it('renders the page heading', () => {
    const wrapper = mount(HomeView);

    expect(wrapper.find('h1').text()).toBe('My board');
  });

  it('renders BoardContainer component', () => {
    const wrapper = mount(HomeView);

    expect(wrapper.find('[data-testid="board-container"]').exists()).toBe(true);
  });
});
