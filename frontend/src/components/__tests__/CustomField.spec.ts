import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomField from '@/components/CustomField.vue';

describe('CustomField.vue', () => {
  describe('Rendering', () => {
    it('renders an input element', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
        },
      });

      const input = wrapper.find('input');
      expect(input.exists()).toBe(true);
    });

    it('renders a label when provided', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
          label: 'Test Label',
        },
      });

      const label = wrapper.find('label');
      expect(label.exists()).toBe(true);
      expect(label.text()).toBe('Test Label');
    });

    it('does not render a label when not provided', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
        },
      });

      expect(wrapper.find('label').exists()).toBe(false);
    });

    it('does not render error message when there is no error', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
        },
      });

      expect(wrapper.find('span.text-red-500').exists()).toBe(false);
    });
  });

  describe('Props', () => {
    describe('name', () => {
      it('passes name to input id attribute', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'username',
          },
        });

        const input = wrapper.find('input');
        expect(input.attributes('id')).toBe('username');
      });

      it('passes name to label for attribute', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'email',
            label: 'Email Address',
          },
        });

        const label = wrapper.find('label');
        expect(label.attributes('for')).toBe('email');
      });
    });

    describe('type', () => {
      it('applies default type "text" when not set', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
          },
        });

        const input = wrapper.find('input');
        expect(input.attributes('type')).toBe('text');
      });

      it('applies type "password" when set', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'password',
            type: 'password',
          },
        });

        const input = wrapper.find('input');
        expect(input.attributes('type')).toBe('password');
      });

      it('applies type "email" when set', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'email',
            type: 'email',
          },
        });

        const input = wrapper.find('input');
        expect(input.attributes('type')).toBe('email');
      });
    });

    describe('as', () => {
      it('renders as input by default', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
          },
        });

        expect(wrapper.find('input').exists()).toBe(true);
        expect(wrapper.find('textarea').exists()).toBe(false);
      });

      it('renders as textarea when set to "textarea"', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'bio',
            as: 'textarea',
          },
        });

        expect(wrapper.find('textarea').exists()).toBe(true);
        expect(wrapper.find('input').exists()).toBe(false);
      });
    });

    describe('placeholder', () => {
      it('passes placeholder to input when provided', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
            placeholder: 'Enter value',
          },
        });

        const input = wrapper.find('input');
        expect(input.attributes('placeholder')).toBe('Enter value');
      });
    });

    describe('direction', () => {
      it('applies default direction "auto"', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
          },
        });

        const container = wrapper.find('div');
        expect(container.attributes('class')).toContain('flex flex-wrap items-center gap-2');
      });

      it('applies direction "horizontal" when set', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
            direction: 'horizontal',
          },
        });

        const container = wrapper.find('div');
        expect(container.attributes('class')).toContain('flex items-center gap-2');
      });

      it('applies direction "vertical" when set', () => {
        const wrapper = mount(CustomField, {
          props: {
            name: 'testField',
            direction: 'vertical',
          },
        });

        const container = wrapper.find('div');
        expect(container.attributes('class')).toContain('flex flex-col gap-2');
      });
    });
  });

  describe('Styles', () => {
    it('has base input styles', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
        },
      });

      const input = wrapper.find('input');
      expect(input.attributes('class')).toContain('block');
      expect(input.attributes('class')).toContain('pl-1');
      expect(input.attributes('class')).toContain('py-1');
      expect(input.attributes('class')).toContain('border');
      expect(input.attributes('class')).toContain('border-neutral-300');
      expect(input.attributes('class')).toContain('rounded-sm');
    });

    it('has focus styles', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
        },
      });

      const input = wrapper.find('input');
      expect(input.attributes('class')).toContain('focus:border-neutral-400');
      expect(input.attributes('class')).toContain('focus:ring-neutral-400');
    });

    it('applies w-full when direction is vertical', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
          direction: 'vertical',
        },
      });

      const input = wrapper.find('input');
      expect(input.attributes('class')).toContain('w-full');
    });

    it('applies flex-grow when direction is not vertical', () => {
      const wrapper = mount(CustomField, {
        props: {
          name: 'testField',
          direction: 'horizontal',
        },
      });

      const input = wrapper.find('input');
      expect(input.attributes('class')).toContain('flex-grow');
    });
  });
});
