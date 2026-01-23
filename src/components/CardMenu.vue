<script lang="ts">
import CustomButton from '@/components/CustomButton.vue';
import { defineComponent } from 'vue';

export default defineComponent({
  components: { CustomButton },
});
</script>

<template>
  <ul class="space-y-1">
    <li>
      <CustomButton variant="transparent">Open Card</CustomButton>
    </li>
    <li>
      <CustomButton variant="transparent">Edit labels</CustomButton>
    </li>
  </ul>
</template>
