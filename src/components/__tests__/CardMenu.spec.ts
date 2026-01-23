import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CardMenu from '@/components/CardMenu.vue';

describe('CardMenu.vue', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardMenu);

    expect(wrapper.text()).toContain('Open Card');
    expect(wrapper.text()).toContain('Edit labels');
  });
});
