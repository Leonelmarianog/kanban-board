import type { NavigationGuardReturn, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export function routerBeforeEach(to: RouteLocationNormalized): NavigationGuardReturn {
  const { isAuthenticated } = useAuthStore();

  // Redirect authenticated users away from guest-only routes.
  if (to.meta.requiresGuest && isAuthenticated) {
    return { name: 'Home' };
  }

  // Redirect unauthenticated users away from protected routes.
  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'Login' };
  }
}
