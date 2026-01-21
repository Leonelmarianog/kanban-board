import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomButton from '@/components/CustomButton.vue';

describe('CustomButton.vue', () => {
  describe('Rendering', () => {
    it('renders the slot content', () => {
      const wrapper = mount(CustomButton, {
        slots: {
          default: 'Click Me',
        },
      });

      expect(wrapper.find('button').text()).toBe('Click Me');
    });

    it('has base styles', () => {
      const wrapper = mount(CustomButton);
      const button = wrapper.find('button');

      expect(button.attributes('class')).toContain('cursor-pointer');
      expect(button.attributes('class')).toContain('rounded-sm');
    });

    it('has styles for the disabled state', () => {
      const wrapper = mount(CustomButton, {
        props: {
          disabled: true,
        },
      });
      const button = wrapper.find('button');

      expect(button.attributes('class')).toContain('cursor-not-allowed');
    });
  });

  describe('Props', () => {
    describe('variant', () => {
      it('applies default variant classes when no variant is provided', () => {
        const wrapper = mount(CustomButton);
        const button = wrapper.find('button');

        expect(button.attributes('class')).toContain('bg-neutral-500');
      });

      it('applies sky variant classes when variant is "sky"', () => {
        const wrapper = mount(CustomButton, {
          props: {
            variant: 'sky',
          },
        });
        const button = wrapper.find('button');

        expect(button.attributes('class')).toContain('bg-sky-500');
      });
    });
  });

  describe('Events', () => {
    it('emits a "click" event when clicked', async () => {
      const wrapper = mount(CustomButton);

      await wrapper.find('button').trigger('click');

      expect(wrapper.emitted()).toHaveProperty('click');
    });
  });
});
