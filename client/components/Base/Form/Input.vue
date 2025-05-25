<script setup lang="ts">
import { Field, ErrorMessage, defineRule } from "vee-validate";

const model = defineModel({
  type: String,
  required: true,
});
const props = defineProps({
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
  type: {
    type: String,
    required: false,
    default: "text",
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
defineRule("min", (value, { min }) => {
  if (value.length < min && props.type === "text") {
    return `Pole musí obsahovat alespoň ${min} znaků.`;
  }
  return true;
});
defineRule("max", (value, { max }) => {
  if (value.length > max && props.type === "text") {
    return `Pole může obsahovat maximálně ${max} znaků.`;
  }
  return true;
});
defineRule("required", (value) => {
  if (!value) {
    return `Pole je povinné.`;
  }
  return true;
});
defineRule("email", (value) => {
  if (!value.includes("@")) {
    return `Pole musí být platný e-mail.`;
  }
  return true;
});
</script>

<template>
  <div>
    <label
      :for="name"
      class="block text-xs lg:text-sm/6 font-medium text-light text-left"
      >{{ label
      }}<span v-if="rules.includes('required')" class="text-danger ml-1"
        >*</span
      ></label
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
      tabindex="-1"
      :class="[
        'mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 text-grayDark shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight text-xs lg:text-sm/6',
        { 'bg-grayLight': disabled },
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
      :min="min > 0 ? min : 0"
      :max="max > 0 ? max : 999999999"
      :autofocus="false"
      tabindex="-1"
      :class="[
        'mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 text-primary shadow-sm ring-1 ring-inset ring-light placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-dark text-xs lg:text-sm/6',
        { 'bg-light': disabled },
      ]"
    />
    <ErrorMessage :name="name" class="text-danger text-sm" />
  </div>
</template>
