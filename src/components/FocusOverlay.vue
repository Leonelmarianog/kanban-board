<template>
  <Teleport to="body">
    <div class="absolute bg-black/50 inset-0 z-1" id="focus-overlay">
      <slot />
    </div>
  </Teleport>
</template>
