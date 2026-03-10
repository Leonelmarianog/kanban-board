<script setup lang="ts">
import { computed } from 'vue';
import { LoaderCircle } from 'lucide-vue-next';
import { useMeQuery } from '@/composables';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const { isPending } = useMeQuery();

const isInitialLoad = computed(() => authStore.isAuthenticated && isPending.value);
</script>

<template>
  <div v-if="isInitialLoad" class="h-screen flex items-center justify-center">
    <LoaderCircle class="animate-spin h-8 w-8 text-blue-500" />
  </div>
  <RouterView v-else />
</template>
