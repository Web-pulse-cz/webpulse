<script setup lang="ts">
import { Field, ErrorMessage, defineRule } from 'vee-validate';

const model = defineModel({
  type: String,
  required: true,
});
defineProps({
  rules: {
    type: String,
    required: false,
    default: '',
  },
  name: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  options: {
    type: Array,
    required: true,
    default: [] | null,
  },
  placeholder: {
    type: String,
    required: false,
    default: '',
  },
  disabled: {
    type: Boolean,
    required: false,
    default: false,
  },
  min: {
    type: Number,
    required: false,
    default: 0,
  },
  max: {
    type: Number,
    required: false,
    default: 0,
  },
  theme: {
    type: String,
    required: false,
    default: 'light',
  },
});
defineRule('required', (value) => {
  if (!value) {
    return `Pole je povinné.`;
  }
  return true;
});
</script>

<template>
  <div>
    <label :for="name" class="block text-xs font-medium text-grayCustom lg:text-sm/6"
      >{{ label }}<span v-if="rules.includes('required')" class="ml-1 text-danger">*</span></label
    >
    <Field
      v-bind="$attrs"
      v-model="model"
      as="select"
      :rules="rules"
      :name="name"
      :placeholder="placeholder"
      :disabled="disabled"
      :autofocus="false"
      :class="[
        theme === 'dark' ? 'bg-gray-900 text-gray-300' : 'bg-white text-grayDark',
        'mt-2 block w-full rounded-md border-0 py-1.5 text-xs shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight lg:py-2 lg:text-sm/6',
        { 'bg-grayLight': disabled },
      ]"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.name }}
      </option>
    </Field>
    <ErrorMessage :name="name" class="text-sm text-danger" />
  </div>
</template>
