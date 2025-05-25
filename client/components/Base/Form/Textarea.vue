<script setup lang="ts">
import { defineRule } from "vee-validate";

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
    <div class="mt-2">
      <Field :name="name" v-model="model" :id="name">
        <textarea
          :id="name"
          v-model="model"
          :rows="rows"
          :name="name"
          :maxlength="max"
          :autofocus="false"
          tabindex="-1"
          :class="[
            'mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 text-primary shadow-sm ring-1 ring-inset ring-light placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-dark text-xs lg:text-sm/6',
            { 'bg-light': disabled },
          ]"
        />
      </Field>
      <p v-if="model" class="text-end text-grayLight text-xs pt-1">
        {{ model.length }} / {{ max }}
      </p>
    </div>
  </div>
</template>
