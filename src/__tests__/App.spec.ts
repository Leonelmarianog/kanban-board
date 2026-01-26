import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import App from '../App.vue';
import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import { createPinia } from 'pinia';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomeView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

describe('App', () => {
  it('Routes correctly', async () => {
    await router.push('/');
    await router.isReady();
    const pinia = createPinia();

    const wrapper = mount(App, {
      global: {
        plugins: [router, pinia],
      },
    });

    expect(wrapper.text()).toContain('My board');
    expect(wrapper.text()).toContain('To Do');
    expect(wrapper.text()).toContain('In Progress');
  });
});
