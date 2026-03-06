import './assets/main.css';
import 'vue-toastification/dist/index.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Toast, { POSITION } from 'vue-toastification';

import App from './App.vue';
import router from './router';
import { VueQueryPlugin } from '@tanstack/vue-query';

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(VueQueryPlugin);
app.use(Toast, {
  position: POSITION.BOTTOM_RIGHT,
});

app.mount('#app');
