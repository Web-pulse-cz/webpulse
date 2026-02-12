<script setup lang="ts">
import { defineRule } from 'vee-validate';
import { defineModel, ref } from 'vue';
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

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
});
defineRule('required', (value) => {
  if (!value) {
    return `Pole je povinné.`;
  }
  return true;
});

const options = ref([
  { name: 'Červená', value: 'red' },
  { name: 'Oranžová', value: 'orange' },
  { name: 'Jantarová', value: 'orange' },
  { name: 'Žlutá', value: 'yellow' },
  { name: 'Limetková', value: 'lime' },
  { name: 'Zelená', value: 'green' },
  { name: 'Emeraldová', value: 'emerald' },
  { name: 'Modrozelená', value: 'teal' },
  { name: 'Azurová', value: 'cyan' },
  { name: 'Nebe', value: 'sky' },
  { name: 'Modrá', value: 'blue' },
  { name: 'Indigo', value: 'indigo' },
  { name: 'Fialová', value: 'violet' },
  { name: 'Fialová', value: 'purple' },
  { name: 'Fuchsie', value: 'fuchsia' },
  { name: 'Růžová', value: 'pink' },
  { name: 'Růžová', value: 'rose' },
  { name: 'Břidlicová', value: 'slate' },
  { name: 'Šedá', value: 'gray' },
  { name: 'Zinková', value: 'zinc' },
  { name: 'Kamenná', value: 'stone' },
  { name: 'Neutrální', value: 'neutral' },
]);
const selectedOption = ref(options.value[0]);

const emit = defineEmits(['update:modelValue']);

watch(selectedOption, () => {
  emit('update:modelValue', selectedOption.value.value);
});

watchEffect(() => {
  if (model.value) {
    selectedOption.value =
      options.value.find((option) => option.value === model.value) || options.value[0];
  }
});
</script>

<template>
  <div class="w-full">
    <Listbox v-model="selectedOption">
      <div class="relative">
        <ListboxLabel :for="name" class="block text-xs font-medium text-grayCustom lg:text-sm/6">
          {{ label }}
        </ListboxLabel>
        <ListboxButton
          :class="[
            'mt-1.5 block w-full rounded-md border-0 py-1 text-xs text-grayDark shadow-sm ring-1 ring-inset ring-grayLight placeholder:text-grayLight focus:ring-1 focus:ring-inset focus:ring-primaryLight lg:py-2 lg:text-sm/6',
            { 'bg-grayLight': disabled },
          ]"
        >
          <PropsBadge :color="selectedOption.value">
            {{ selectedOption.name }}
          </PropsBadge>
          <span class="pointer-events-none absolute inset-y-0 right-0 top-8 flex items-center pr-2">
            <ChevronUpDownIcon class="h-5 w-5 text-grayCustom" aria-hidden="true" />
          </span>
        </ListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions
            class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
          >
            <ListboxOption
              v-for="(option, index) in options"
              v-slot="{ active, selected }"
              :key="index"
              :value="option"
              as="template"
            >
              <li
                :class="[
                  active ? 'bg-secondaryLight' : 'text-gray-900',
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                ]"
              >
                <span
                  ><PropsBadge :color="option.value">{{ option.name }}</PropsBadge></span
                >
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-primaryCustom"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>
  </div>
</template>
