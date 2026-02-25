import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DynamicForm from '@/components/DynamicForm.vue';
import { object, string } from 'yup';

describe('DynamicForm.vue', () => {
  it('renders the slotted content', () => {
    const wrapper = mount(DynamicForm, {
      slots: {
        default: '<div id="test-slot">Slot Content</div>',
      },
    });

    expect(wrapper.find('#test-slot').exists()).toBe(true);
    expect(wrapper.text()).toContain('Slot Content');
  });

  it('renders a form element', () => {
    const wrapper = mount(DynamicForm);

    expect(wrapper.find('form').exists()).toBe(true);
  });

  it('passes errors to slot', () => {
    const wrapper = mount(DynamicForm, {
      slots: {
        default: `
        <template #default="slotProps">
          <span id="errors">{{ JSON.stringify(slotProps.errors) }}</span>
        </template>
      `,
      },
    });

    expect(wrapper.find('#errors').exists()).toBe(true);
  });

  it('passes values to slot', () => {
    const wrapper = mount(DynamicForm, {
      slots: {
        default: `
        <template #default="slotProps">
          <span id="values">{{ JSON.stringify(slotProps.values) }}</span>
        </template>
      `,
      },
    });

    expect(wrapper.find('#values').exists()).toBe(true);
  });

  it('passes meta to slot', () => {
    const wrapper = mount(DynamicForm, {
      slots: {
        default: `
        <template #default="slotProps">
          <span id="meta">{{ JSON.stringify(slotProps.meta) }}</span>
        </template>
      `,
      },
    });

    expect(wrapper.find('#meta').exists()).toBe(true);
  });

  it('emits "submit" when form is submitted', async () => {
    const wrapper = mount(DynamicForm);

    await wrapper.find('form').trigger('submit');

    expect(wrapper.emitted('submit')).toBeTruthy();
  });

  it('emits submit with form values', async () => {
    const wrapper = mount(DynamicForm, {
      props: {
        initialValues: { test: 'initial' },
      },
    });

    await wrapper.find('form').trigger('submit');

    expect(wrapper.emitted('submit')).toBeTruthy();
    expect(wrapper.emitted('submit')![0]).toEqual([{ test: 'initial' }]);
  });

  it('provides slot props with validation schema', () => {
    const schema = object({
      test: string().required(),
    });

    const wrapper = mount(DynamicForm, {
      props: {
        schema,
      },
      slots: {
        default: `
        <template #default="slotProps">
          <span id="meta">{{ slotProps.meta }}</span>
        </template>
      `,
      },
    });

    expect(wrapper.find('#meta').exists()).toBe(true);
  });
});
