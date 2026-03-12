<script setup lang="ts">
import PageHeaderMenu from './PageHeaderMenu.vue';
</script>

<template>
  <header class="bg-black/20 py-4 shadow-sm flex items-center justify-between px-4">
    <h1 data-test="page-header.heading" class="font-bold text-3xl text-center">My board</h1>

    <PageHeaderMenu />
  </header>
</template>
