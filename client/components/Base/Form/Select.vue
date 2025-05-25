<script setup lang="ts">
import { Field, ErrorMessage, defineRule } from "vee-validate";

const model = defineModel({
  type: String,
  required: true,
});
defineProps({
  rules: {
    type: String,
    required: false,
    default: "",
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
    default: "",
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
defineRule("required", (value) => {
  if (!value) {
    return `Pole je povinné.`;
  }
  return true;
});
</script>

<template>
  <div>
    <label :for="name" class="block text-xs lg:text-sm/6 font-medium text-light"
      >{{ label
      }}<span v-if="rules.includes('required')" class="text-danger ml-1"
        >*</span
      ></label
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
      tabindex="-1"
      :class="[
        'mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 text-primary shadow-sm ring-1 ring-inset ring-light placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-dark text-xs lg:text-sm/6',
        { 'bg-light': disabled },
      ]"
    >
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.name }}
      </option>
    </Field>
    <ErrorMessage :name="name" class="text-danger text-sm" />
  </div>
</template>
