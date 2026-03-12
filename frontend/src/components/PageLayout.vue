<script setup lang="ts">
import PageHeader from './PageHeader.vue';
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 flex flex-col gap-8 relative">
    <PageHeader />

    <main data-test="page-layout.content" class="h-full relative">
      <div class="h-full overflow-x-auto">
        <slot name="default" />
      </div>
    </main>
  </div>
</template>
