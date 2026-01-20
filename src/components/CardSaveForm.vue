<script lang="ts">
import { defineComponent } from 'vue';
import { Field } from 'vee-validate';
import * as Yup from 'yup';
import DynamicForm from './DynamicForm.vue';

export default defineComponent({
  components: { DynamicForm, Field },

  emits: ['save', 'cancel'],

  data: () => {
    const formSchema = Yup.object({
      content: Yup.string().trim().required('This field is required.'),
    });

    return {
      formSchema,
    };
  },

  methods: {
    onSubmit(values: Record<string, unknown>) {
      this.$emit('save', values);
    },

    onCancelClick() {
      this.$emit('cancel');
    },
  },

  mounted() {
    const $contentField = (this.$refs.contentFieldWrapper as HTMLElement).querySelector(
      'textarea[name="content"]',
    )! as HTMLTextAreaElement;
    $contentField.focus();
  },
});
</script>

<template>
  <DynamicForm :schema="formSchema" @submit="onSubmit">
    <template #default="{ meta }">
      <div class="space-y-2">
        <div>
          <div class="p-2 bg-white rounded-sm shadow-md" ref="contentFieldWrapper">
            <Field
              as="textarea"
              type="textarea"
              name="content"
              placeholder="Enter a title for this card..."
              class="w-full block pl-1"
              ref="contentInput"
            />
          </div>
        </div>

        <div class="space-x-2">
          <button
            class="bg-purple-500 text-white text-sm cursor-pointer rounded-sm py-2 px-6 hover:bg-purple-400 disabled:bg-purple-400 disabled:cursor-not-allowed"
            type="submit"
            :disabled="!meta.valid"
          >
            Save
          </button>
          <button
            class="bg-neutral-500 text-white text-sm hover:bg-neutral-400 cursor-pointer rounded-sm py-2 px-6"
            type="button"
            @click="onCancelClick"
          >
            Cancel
          </button>
        </div>
      </div>
    </template>
  </DynamicForm>
</template>
