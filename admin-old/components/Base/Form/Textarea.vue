<script setup lang="ts">
import { defineRule } from 'vee-validate';

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
    <div class="mt-2">
      <textarea
        :id="name"
        v-model="model"
        :rows="rows"
        :name="name"
        :maxlength="max"
        :autofocus="false"
        tabindex="-1"
        :class="[
          'mt-2 block w-full rounded-md border-0 py-1.5 text-xs text-grayDark shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight lg:py-2 lg:text-sm/6',
          { 'bg-grayLight': disabled },
        ]"
      />
      <p v-if="model" class="pt-1 text-end text-xs text-grayLight">
        {{ model.length }} / {{ max }}
      </p>
    </div>
  </div>
</template>
