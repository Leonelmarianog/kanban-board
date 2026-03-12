import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PageHeader from '../PageHeader.vue';

vi.mock('../PageHeaderMenu.vue', () => ({
  default: {
    name: 'PageHeaderMenu',
    template: '<div data-test="page-header-menu">PageHeaderMenu</div>',
  },
}));

describe('PageHeader', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should render the heading', () => {
    const wrapper = mount(PageHeader);

    expect(wrapper.find('[data-test="page-header.heading"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="page-header.heading"]').text()).toBe('My board');
  });

  it('should render PageHeaderMenu', () => {
    const wrapper = mount(PageHeader);

    expect(wrapper.find('[data-test="page-header-menu"]').exists()).toBe(true);
  });
});
