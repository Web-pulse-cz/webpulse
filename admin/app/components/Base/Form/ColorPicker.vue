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
        <ListboxLabel :for="name" class="mb-1.5 block text-sm font-medium text-slate-700">
          {{ label }}
        </ListboxLabel>

        <ListboxButton
          :class="[
            disabled
              ? 'cursor-not-allowed bg-slate-100 opacity-75'
              : 'cursor-pointer bg-white hover:border-slate-400',
            'relative w-full rounded-xl py-2.5 pl-4 pr-10 text-left text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500',
          ]"
        >
          <span class="block truncate">
            <PropsBadge :color="selectedOption.value">
              {{ selectedOption.name }}
            </PropsBadge>
          </span>
          <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <ChevronUpDownIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
          </span>
        </ListboxButton>

        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform scale-95 opacity-0 translate-y-1"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 translate-y-1"
        >
          <ListboxOptions
            class="absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white p-1.5 text-base shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none sm:text-sm"
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
                  active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700',
                  'relative cursor-pointer select-none rounded-xl py-2.5 pl-10 pr-4 transition-colors duration-150',
                ]"
              >
                <span class="block truncate">
                  <PropsBadge :color="option.value">{{ option.name }}</PropsBadge>
                </span>
                <span
                  v-if="selected"
                  :class="[
                    active ? 'text-indigo-700' : 'text-indigo-600',
                    'absolute inset-y-0 left-0 flex items-center pl-3',
                  ]"
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
