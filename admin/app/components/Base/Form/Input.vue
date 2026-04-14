<script setup lang="ts">
import { Field, ErrorMessage, defineRule } from 'vee-validate';

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
defineRule('min', (value, { min }) => {
  if (value.length < min && props.type === 'text') {
    return `Pole musí obsahovat alespoň ${min} znaků.`;
  }
  return true;
});
defineRule('max', (value, { max }) => {
  if (value.length > max && props.type === 'text') {
    return `Pole může obsahovat maximálně ${max} znaků.`;
  }
  return true;
});
defineRule('required', (value) => {
  if (!value) {
    return `Pole je povinné.`;
  }
  return true;
});
defineRule('email', (value) => {
  if (!value.includes('@')) {
    return `Pole musí být platný e-mail.`;
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
      v-if="
        type === 'text' ||
        type === 'email' ||
        type === 'password' ||
        type === 'datetime-local' ||
        type === 'date' ||
        type === 'time'
      "
      v-bind="$attrs"
      v-model="model"
      :rules="rules"
      :name="name"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      aria-autocomplete="none"
      autocomplete="off"
      :autofocus="false"
      :class="[
        disabled
          ? 'cursor-not-allowed bg-slate-50 text-slate-500 ring-slate-200'
          : 'bg-white text-slate-900 ring-slate-300 hover:ring-slate-400',
        'block w-full rounded-xl border-0 px-4 py-2.5 text-sm shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500',
      ]"
    />

    <Field
      v-else-if="type === 'number'"
      v-bind="$attrs"
      v-model="model"
      :rules="rules"
      :name="name"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      aria-autocomplete="none"
      autocomplete="off"
      :min="min >= 0 ? min : 3"
      :max="max > 0 ? max : 45"
      :step="step"
      :autofocus="false"
      :class="[
        disabled
          ? 'cursor-not-allowed bg-slate-50 text-slate-500 ring-slate-200'
          : 'bg-white text-slate-900 ring-slate-300 hover:ring-slate-400',
        'block w-full rounded-xl border-0 px-4 py-2.5 text-sm shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500',
      ]"
    />

    <ErrorMessage :name="name" class="mt-1.5 block text-xs font-medium text-red-500" />
  </div>
</template>
