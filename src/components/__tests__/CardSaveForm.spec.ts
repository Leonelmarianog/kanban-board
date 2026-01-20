import { describe, it, expect, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import CardSaveForm from '@/components/CardSaveForm.vue';
import waitForExpect from 'wait-for-expect';

describe('CardSaveForm.vue', () => {
  describe('Rendering', () => {
    it('renders correctly', () => {
      const wrapper = mount(CardSaveForm);

      expect(wrapper.find('textarea[name="content"]').exists()).toBe(true);
      expect(wrapper.find('textarea[name="content"]').attributes('placeholder')).toBeTruthy();
      expect(wrapper.find('button[type="submit"]').text()).toBe('Save');
      expect(wrapper.find('button[type="button"]').text()).toBe('Cancel');
    });

    it('focuses the content field on mount', () => {
      const spy = vi.spyOn(HTMLTextAreaElement.prototype, 'focus');
      mount(CardSaveForm);

      expect(spy).toHaveBeenCalled();
      spy.mockRestore();
    });
  });

  describe('Fields', () => {
    describe('Content Field', () => {
      it('is required', async () => {
        const wrapper = mount(CardSaveForm);
        const contentField = wrapper.find('textarea[name="content"]');

        await contentField.setValue('');

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
        });
      });

      it('trims input', async () => {
        const wrapper = mount(CardSaveForm);
        const contentField = wrapper.find('textarea[name="content"]');

        await contentField.setValue('       ');

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
        });
      });
    });
  });

  describe('Save and Cancel buttons', () => {
    describe('Save button', () => {
      it('is disabled on mount', async () => {
        const wrapper = mount(CardSaveForm);

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
        });
      });

      it('is disabled when validation fails', async () => {
        const wrapper = mount(CardSaveForm);
        const contentField = wrapper.find('textarea[name="content"]');

        await contentField.setValue('');

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
        });
      });

      it('is enabled when validation passes', async () => {
        const wrapper = mount(CardSaveForm);
        const contentField = wrapper.find('textarea[name="content"]');

        await contentField.setValue('test');

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeUndefined();
        });
      });
    });

    describe('Cancel button', () => {
      it('emits a "cancel" event when clicked', async () => {
        const wrapper = mount(CardSaveForm);

        await wrapper.find('button[type="button"]').trigger('click');

        await flushPromises();
        await waitForExpect(() => {
          expect(wrapper.emitted()).toHaveProperty('cancel');
        });
      });
    });
  });

  describe('Submission', () => {
    it('emits a "save" event on submit', async () => {
      const wrapper = mount(CardSaveForm);

      await wrapper.find('textarea[name="content"]').setValue('test');
      await wrapper.find('form').trigger('submit');

      await flushPromises();
      await waitForExpect(() => {
        expect(wrapper.emitted()).toHaveProperty('save');
      });
    });
  });
});
