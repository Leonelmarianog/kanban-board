<script setup lang="ts">
import { computed, onMounted, useTemplateRef } from 'vue';
import { Field } from 'vee-validate';
import { object, string } from 'yup';
import DynamicForm from '@/components/DynamicForm.vue';
import CustomButton from '@/components/CustomButton.vue';

const schema = object({
  content: string().trim().required('This field is required.').default(''),
});

const props = defineProps<{
  initialValues?: Record<string, unknown> | null;
}>();

const emit = defineEmits<{
  (e: 'save', values: Record<string, unknown>): void;
  (e: 'cancel'): void;
}>();

const contentFieldWrapper = useTemplateRef<HTMLElement>('contentFieldWrapper');

const isEdit = computed(() => !!props.initialValues);

const formValues = computed(() => (isEdit.value ? props.initialValues : null));

const save = (values: Record<string, unknown>) => {
  emit('save', values);
};

const cancel = () => {
  emit('cancel');
};

onMounted(() => {
  const $contentField = contentFieldWrapper.value?.querySelector(
    'textarea[name="content"]',
  ) as HTMLTextAreaElement | null;
  $contentField?.focus();
});
</script>

<template>
  <DynamicForm :schema="schema" :initial-values="formValues" @submit="save">
    <template #default="{ meta }">
      <div class="space-y-2">
        <div>
          <div class="p-2 bg-neutral-100 rounded-sm shadow-md" ref="contentFieldWrapper">
            <Field
              as="textarea"
              type="textarea"
              name="content"
              placeholder="Enter a title for this card..."
              class="w-full block pl-1"
            />
          </div>
        </div>

        <div class="space-x-2">
          <CustomButton type="submit" variant="sky" :disabled="!meta.valid">Save</CustomButton>
          <CustomButton type="button" @click="cancel">Cancel</CustomButton>
        </div>
      </div>
    </template>
  </DynamicForm>
</template>
