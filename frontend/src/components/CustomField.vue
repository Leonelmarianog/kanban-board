<script setup lang="ts">
import { ErrorMessage, Field } from 'vee-validate';
import { computed } from 'vue';

enum InputAs {
  Input = 'input',
  Textarea = 'textarea',
}

enum InputType {
  Text = 'text',
  Password = 'password',
  Email = 'email',
}

enum DirectionOption {
  Horizontal = 'horizontal',
  Vertical = 'vertical',
  Auto = 'auto',
}

const props = withDefaults(
  defineProps<{
    as?: InputAs;
    type?: InputType;
    name: string;
    placeholder?: string;
    label?: string;
    direction?: DirectionOption;
  }>(),
  {
    as: InputAs.Input,
    type: InputType.Text,
    placeholder: undefined,
    label: undefined,
    direction: DirectionOption.Auto,
  },
);

const DIRECTION_OPTIONS: Record<DirectionOption, string> = {
  [DirectionOption.Horizontal]: 'flex items-center gap-2',
  [DirectionOption.Vertical]: 'flex flex-col gap-2',
  [DirectionOption.Auto]: 'flex flex-wrap items-center gap-2',
};

const containerClasses = computed(() => {
  const direction = DIRECTION_OPTIONS[props.direction];
  return [direction];
});

const inputClasses = computed(() => [
  'block pl-1 py-1 border border-neutral-300 rounded-sm focus:border-neutral-400 focus:ring-neutral-400 focus:ring-1 focus:ring-opacity-50',
  props.direction === DirectionOption.Vertical ? 'w-full' : 'flex-grow',
]);
</script>

<template>
  <div :class="containerClasses">
    <label :for="name" class="block text-sm font-bold text-nowrap m-0 p-0" v-if="label">{{
      label
    }}</label>

    <Field
      :id="name"
      :as="as"
      :type="type"
      :name="name"
      :placeholder="placeholder"
      :class="inputClasses"
    />

    <ErrorMessage :name="name" class="text-red-500 text-xs italic" />
  </div>
</template>
