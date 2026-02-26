<script setup lang="ts">
import { useLogout } from '@/composables/useLogout';
import { useAuthStore } from '@/stores/auth';

const { logout, isLoading } = useLogout();
const authStore = useAuthStore();
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 flex flex-col gap-8 relative">
    <header class="bg-black/20 py-4 shadow-sm flex items-center justify-between px-4">
      <slot name="heading" />

      <button
        v-if="authStore.isAuthenticated"
        @click="logout()"
        :disabled="isLoading"
        class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoading ? 'Logging out...' : 'Logout' }}
      </button>
    </header>

    <main class="h-full relative">
      <div class="h-full overflow-x-auto">
        <slot name="default" />
      </div>
    </main>
  </div>
</template>
