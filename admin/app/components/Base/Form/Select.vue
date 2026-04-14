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
  <div class="w-full">
    <label :for="name" class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="rules && rules.includes('required')" class="ml-1 text-red-500">*</span>
    </label>

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
        'block w-full rounded-xl border-0 py-2.5 pl-4 pr-10 text-sm shadow-sm ring-1 ring-inset transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500',
        disabled
          ? theme === 'dark'
            ? 'cursor-not-allowed bg-zinc-900/50 text-zinc-500 ring-zinc-800'
            : 'cursor-not-allowed bg-slate-50 text-slate-400 ring-slate-200'
          : theme === 'dark'
            ? 'bg-zinc-900 text-zinc-300 ring-zinc-700 hover:ring-zinc-600'
            : 'bg-white text-slate-900 ring-slate-300 hover:ring-slate-400',
      ]"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.name }}
      </option>
    </Field>

    <ErrorMessage :name="name" class="mt-1.5 block text-xs font-medium text-red-500" />
  </div>
</template>
