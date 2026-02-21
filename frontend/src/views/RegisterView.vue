<script setup lang="ts">
import RegisterForm from '@/components/RegisterForm.vue';
</script>

<template>
  <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 min-w-[15em]">
    <main class="h-full">
      <div class="h-full flex justify-center items-center">
        <div class="w-[25em]">
          <RegisterForm />
        </div>
      </div>
    </main>
  </div>
</template>
