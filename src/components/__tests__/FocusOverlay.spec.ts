import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FocusOverlay from '@/components/FocusOverlay.vue';

describe('FocusOverlay.vue', () => {
  it('renders the slotted content', () => {
    mount(FocusOverlay, {
      slots: {
        default: '<span id="slotted">Content</span>',
      },
    });
    const slotted = document.body.querySelector('#slotted');

    expect(slotted).not.toBeNull();
  });

  it('teleports the overlay to the body', () => {
    mount(FocusOverlay);
    const overlay = document.querySelector('body > #focus-overlay');

    expect(overlay).not.toBeNull();
  });
});
