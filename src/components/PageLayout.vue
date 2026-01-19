<script lang="ts">
export default {
  data() {
    return {
      isOverlayVisible: false,
    };
  },

  methods: {
    toggleOverlay() {
      this.isOverlayVisible = !this.isOverlayVisible;
    },
  },

  provide() {
    return {
      toggleLayoutOverlay: this.toggleOverlay,
    };
  },
};
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 flex flex-col gap-8 relative">
    <header class="bg-black/20 py-4 shadow-sm">
      <slot name="heading" />
    </header>

    <main class="h-full relative">
      <div class="h-full px-2 overflow-x-auto">
        <slot name="default" />
      </div>
    </main>

    <div v-if="isOverlayVisible" class="bg-black/50 absolute inset-0 z-1"></div>
  </div>
</template>
