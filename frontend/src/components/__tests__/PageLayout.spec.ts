import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PageLayout from '../PageLayout.vue';

vi.mock('../PageHeader.vue', () => ({
  default: {
    name: 'PageHeader',
    template: '<header data-test="page-header">PageHeader</header>',
  },
}));

describe('PageLayout', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
  });

  it('should render PageHeader', () => {
    const wrapper = mount(PageLayout);

    expect(wrapper.find('[data-test="page-header"]').exists()).toBe(true);
  });

  it('should render slotted content', () => {
    const wrapper = mount(PageLayout, {
      slots: {
        default: '<div data-test="page-layout.slot-content">Main content</div>',
      },
    });

    expect(wrapper.find('[data-test="page-layout.content"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="page-layout.slot-content"]').text()).toBe('Main content');
  });
});
