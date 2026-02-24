<script setup lang="ts">
import { useField } from 'vee-validate';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    as?: 'input' | 'textarea';
    type?: 'text' | 'password' | 'email';
    name: string;
    placeholder?: string;
    label?: string;
    direction?: 'horizontal' | 'vertical' | 'auto';
  }>(),
  {
    as: 'input',
    type: 'text',
    placeholder: undefined,
    label: undefined,
    direction: 'auto',
  },
);

const { value, handleChange, handleBlur, errors } = useField(() => props.name);

const DIRECTION_OPTIONS = {
  horizontal: 'flex items-center gap-2',
  vertical: 'flex flex-col gap-2',
  auto: 'flex flex-wrap items-center gap-2',
};

const containerClasses = computed(() => {
  const direction = DIRECTION_OPTIONS[props.direction];
  return [direction];
});

const inputClasses = computed(() => [
  'block pl-1 py-1 border border-neutral-300 rounded-sm focus:border-neutral-400 focus:ring-neutral-400 focus:ring-1 focus:ring-opacity-50',
  props.direction === 'vertical' ? 'w-full' : 'flex-grow',
  errors.value.length > 0 ? 'border-red-500' : '',
]);

const inputType = computed(() => props.type);

const tagName = computed(() => props.as);
</script>

<template>
  <div :class="containerClasses">
    <label :for="name" class="block text-sm font-bold text-nowrap m-0 p-0" v-if="label">{{
      label
    }}</label>

    <component
      :is="tagName"
      :id="name"
      :type="inputType"
      :name="name"
      :placeholder="placeholder"
      :class="inputClasses"
      :value="value"
      @input="handleChange"
      @blur="handleBlur"
    />

    <ul v-if="errors.length" class="list-disc list-inside">
      <li v-for="error in errors" :key="error" class="text-red-500 text-xs italic m-0 p-0">
        {{ error }}
      </li>
    </ul>
  </div>
</template>
