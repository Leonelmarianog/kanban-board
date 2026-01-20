<script lang="ts">
import { defineComponent } from 'vue';
import { Form as VeeForm } from 'vee-validate';

export default defineComponent({
  components: { VeeForm },

  emits: ['submit'],

  props: {
    schema: {
      type: Object,
      default: undefined,
    },
  },

  methods: {
    onSubmit(values: Record<string, unknown>) {
      this.$emit('submit', values);
    },
  },
});
</script>

<template>
  <VeeForm :validation-schema="schema" @submit="onSubmit" v-slot="{ errors, values, meta }">
    <slot :errors="errors" :values="values" :meta="meta" />
  </VeeForm>
</template>
