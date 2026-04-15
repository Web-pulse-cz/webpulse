<script setup lang="ts">
import { Field, ErrorMessage } from 'vee-validate';

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
  rows: {
    type: Number,
    required: false,
    default: 4,
  },
  max: {
    type: Number,
    required: false,
    default: 255,
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
      <textarea
        v-bind="field"
        :id="name"
        :rows="rows"
        :maxlength="max"
        :autofocus="false"
        :disabled="disabled"
        :placeholder="placeholder"
        :class="[
          disabled
            ? 'cursor-not-allowed bg-slate-50 text-slate-500 ring-slate-200'
            : errors.length
              ? 'bg-white text-slate-900 ring-red-400 hover:ring-red-500'
              : 'bg-white text-slate-900 ring-slate-300 hover:ring-slate-400',
          'block w-full resize-y rounded-xl border-0 px-4 py-2.5 text-sm shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500',
        ]"
      />
    </Field>

    <ErrorMessage :name="name" class="mt-1.5 block text-xs font-medium text-red-500" />

    <div v-if="model" class="mt-1.5 flex justify-end">
      <p class="text-xs font-medium text-slate-400">{{ model.length }} / {{ max }}</p>
    </div>
  </div>
</template>
