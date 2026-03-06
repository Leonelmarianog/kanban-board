<script setup lang="ts">
import { watch } from 'vue';
import { useRouter } from 'vue-router';
import { useMeQuery } from '@/composables';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const { isError } = useMeQuery();

watch(isError, (hasError) => {
  if (hasError && authStore.isAuthenticated) {
    authStore.clearAuth();
    router.push({ name: 'Login' });
  }
});
</script>

<template>
  <RouterView />
</template>
