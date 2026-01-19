<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  mounted() {
    (this.$refs.textArea as HTMLTextAreaElement).focus();
  },

  data() {
    return {
      content: this.initialContent,
    };
  },

  props: {
    initialContent: {
      type: String,
      default: '',
    },
  },

  methods: {
    handleSubmit() {
      const formData = { content: this.content };
      this.$emit('update-card', formData);
      this.content = '';
    },

    handleCancel() {
      this.$emit('close');
    },
  },
});
</script>

<template>
  <form @submit.prevent="handleSubmit()">
    <textarea
      name="content"
      v-model="content"
      class="px-2 py-1 w-full"
      ref="textArea"
      placeholder="Enter a title for this card..."
    />

    <div class="flex gap-2 text-sm">
      <button
        type="submit"
        class="bg-purple-500 text-white text-sm hover:bg-purple-400 cursor-pointer rounded-md py-1 px-4"
      >
        Add
      </button>
      <button
        type="button"
        class="bg-neutral-500 text-white text-sm hover:bg-neutral-400 cursor-pointer rounded-md py-1 px-4"
        @click="handleCancel"
      >
        Cancel
      </button>
    </div>
  </form>
</template>
