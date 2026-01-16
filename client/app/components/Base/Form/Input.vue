<script setup lang="ts">
import { Field, ErrorMessage, defineRule } from 'vee-validate';

const labelClasses = {
  dark: 'block text-xs lg:text-sm/6 font-medium text-primary',
  light: 'block text-xs lg:text-sm/6 font-medium text-light',
  newsletter: 'block text-xs lg:text-sm/6 font-medium text-white',
};

const inputClasses = {
  light: 'ring-primary focus:ring-light',
  dark: 'ring-primary focus:ring-primary',
  newsletter: 'ring-[#707070] focus:ring-[#707070]',
};

const model = defineModel({
  type: String,
  required: true,
});
const props = defineProps({
  variant: {
    type: String,
    required: false,
    default: 'dark',
  },
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

const labelClass = computed(() => {
  return labelClasses[props.variant] || labelClasses.dark;
});

const inputClass = computed(() => {
  return `text-primary mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 shadow-sm ring-1 ring-inset focus:ring-1 focus:ring-inset text-xs lg:text-sm/6 ${inputClasses[props.variant]}`;
});
</script>

<template>
  <div>
    <label :for="name" :class="labelClass"
      >{{ label }}<span v-if="rules.includes('required')" class="text-danger ml-1">*</span></label
    >
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
      :class="[inputClass, { 'bg-grayLight': disabled }]"
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
      :min="min > 0 ? min : 0"
      :max="max > 0 ? max : 999999999"
      :autofocus="false"
      :class="[inputClass, { 'bg-grayLight': disabled }]"
    />
    <ErrorMessage :name="name" class="text-danger text-xs" />
  </div>
</template>
