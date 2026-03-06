import { mount, VueWrapper, MountingOptions } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';
import type { Component } from 'vue';

/**
 * Fill form inputs by their name attribute
 */
export async function fillForm(
  wrapper: VueWrapper<Component>,
  fields: Record<string, string>,
): Promise<void> {
  for (const [name, value] of Object.entries(fields)) {
    const input = wrapper.find(`input[name="${name}"]`);
    if (input.exists()) {
      await input.setValue(value);
    }
  }
}

/**
 * Submit a form
 */
export async function submitForm(wrapper: VueWrapper<Component>): Promise<void> {
  const form = wrapper.find('form');
  await form.trigger('submit');
}

/**
 * Mount a component with Pinia and Vue Query plugins
 */
export function mountWithPlugins<T extends Component>(component: T, options?: MountingOptions<T>) {
  const pinia = createPinia();
  setActivePinia(pinia);

  const queryClient = new QueryClient({
    defaultOptions: {
      queries: { retry: false },
      mutations: { retry: false },
    },
  });

  return mount(component, {
    ...options,
    global: {
      ...options?.global,
      plugins: [pinia, [VueQueryPlugin, { queryClient }]],
    },
  }) as VueWrapper<T>;
}
