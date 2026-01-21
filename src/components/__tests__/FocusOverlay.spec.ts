import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FocusOverlay from '@/components/FocusOverlay.vue';

describe('FocusOverlay.vue', () => {
  it('renders the slot content', () => {
    const wrapper = mount(FocusOverlay, {
      slots: {
        default: '<span id="slotted">Content</span>',
      },
    });
    const overlay = wrapper.find('#slotted');

    expect(overlay.exists()).toBe(true);
  });

  it('teleports the overlay to the body', () => {
    mount(FocusOverlay);
    const overlay = document.body.querySelector('#focus-overlay');

    expect(overlay).not.toBeNull();
  });

  it('renders slotted content on top of the overlay', () => {
    const wrapper = mount(FocusOverlay, {
      slots: {
        default: '<span id="slotted">Content</span>',
      },
    });
    const slotted = wrapper.find('#slotted').element.parentElement;
    const overlay = document.body.querySelector('#focus-overlay');

    expect(slotted?.classList.contains('z-2')).toBe(true);
    expect(overlay?.classList.contains('z-1')).toBe(true);
  });
});
