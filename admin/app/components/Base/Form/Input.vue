<script setup lang="ts">
import { Field, ErrorMessage } from 'vee-validate';

const model = defineModel({
  type: String,
  required: true,
});
const props = defineProps({
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
  type: {
    type: String,
    required: false,
    default: 'text',
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
  step: {
    type: Number,
    required: false,
    default: 1,
  },
});
</script>

<template>
  <div class="w-full">
    <label :for="name" class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="rules && rules.includes('required')" class="ml-1 text-red-500">*</span>
    </label>

    <Field v-slot="{ field, errors }" v-model="model" :rules="rules" :name="name">
      <input
        v-bind="{ ...$attrs, ...field }"
        :id="name"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        aria-autocomplete="none"
        autocomplete="off"
        :autofocus="false"
        :min="type === 'number' ? (min >= 0 ? min : 3) : undefined"
        :max="type === 'number' ? (max > 0 ? max : 45) : undefined"
        :step="type === 'number' ? step : undefined"
        :class="[
          disabled
            ? 'cursor-not-allowed bg-slate-50 text-slate-500 ring-slate-200'
            : errors.length
              ? 'bg-white text-slate-900 ring-red-400 hover:ring-red-500'
              : 'bg-white text-slate-900 ring-slate-300 hover:ring-slate-400',
          'block w-full rounded-xl border-0 px-4 py-2.5 text-sm shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500',
        ]"
      />
    </Field>

    <ErrorMessage :name="name" class="mt-1.5 block text-xs font-medium text-red-500" />
  </div>
</template>
