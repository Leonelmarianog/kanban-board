import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '@/views/HomeView.vue';
import RegisterView from '@/views/RegisterView.vue';
import LoginView from '@/views/LoginView.vue';
import { useAuthStore } from '@/stores/auth';

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

router.beforeEach((to) => {
  const authStore = useAuthStore();

  // Redirect authenticated users away from guest-only routes (login/register)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return { name: 'Home' };
  }

  // Redirect unauthenticated users away from protected routes
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'Login' };
  }
});

export default router;
