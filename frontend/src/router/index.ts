import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '@/views/HomeView.vue';
import RegisterView from '@/views/RegisterView.vue';
import LoginView from '@/views/LoginView.vue';
import { routerBeforeEach } from './guards';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: HomeView,
      meta: { requiresAuth: true },
    },
    {
      path: '/register',
      name: 'Register',
      component: RegisterView,
      meta: { requiresGuest: true },
    },
    {
      path: '/login',
      name: 'Login',
      component: LoginView,
      meta: { requiresGuest: true },
    },
  ],
});

router.beforeEach(routerBeforeEach);

export default router;
