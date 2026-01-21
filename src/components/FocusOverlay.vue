<template>
  <div class="relative z-2">
    <slot />
  </div>

  <Teleport to="body">
    <div class="absolute bg-black/50 inset-0 z-1" id="focus-overlay"></div>
  </Teleport>
</template>
