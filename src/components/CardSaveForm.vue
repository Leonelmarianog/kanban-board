<script lang="ts">
import { defineComponent } from 'vue';
import { Field } from 'vee-validate';
import * as Yup from 'yup';
import DynamicForm from './DynamicForm.vue';
import CustomButton from '@/components/CustomButton.vue';

export default defineComponent({
  components: { CustomButton, DynamicForm, Field },

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
          <CustomButton type="submit" variant="sky" :disabled="!meta.valid">Save</CustomButton>
          <CustomButton type="button" @click="onCancelClick">Cancel</CustomButton>
        </div>
      </div>
    </template>
  </DynamicForm>
</template>
