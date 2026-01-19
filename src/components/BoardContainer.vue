<template>
  <div id="board" class="grid auto-cols-[20em] grid-flow-col gap-2 items-start h-full">
    <slot name="default" />

    <button
      class="bg-black/20 py-4 rounded-md shadow-sm cursor-pointer hover:bg-black/10 font-bold text-sm text-black"
    >
      + Add another list
    </button>
  </div>
</template>
