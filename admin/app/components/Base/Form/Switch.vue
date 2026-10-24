<script setup lang="ts">
import { Switch } from '@headlessui/vue';

const enabled = defineModel('enabled', {
  type: Boolean,
  default: true,
});

defineProps({
  disabledText: {
    type: String,
    required: false,
    default: '',
  },
  enabledText: {
    type: String,
    required: false,
    default: '',
  },
  size: {
    type: String as () => 'md' | 'sm',
    required: false,
    default: 'md',
  },
});
</script>

<template>
  <div
    class="flex w-full items-center justify-between text-sm font-medium sm:w-auto sm:justify-start"
  >
    <span
      v-if="disabledText"
      :class="[
        enabled ? 'text-slate-400' : 'text-slate-900',
        'cursor-default select-none transition-colors duration-200',
      ]"
    >
      {{ disabledText }}
    </span>

    <Switch
      v-model="enabled"
      :class="[
        enabled ? 'bg-indigo-600' : 'bg-slate-200',
        size === 'sm' ? 'h-6 w-11' : 'h-7 w-16',
        { 'ml-3': disabledText, 'mr-3': enabledText },
      ]"
      class="relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
    >
      <span
        aria-hidden="true"
        :class="[
          size === 'sm' ? 'h-5 w-5' : 'h-6 w-6',
          enabled ? (size === 'sm' ? 'translate-x-5' : 'translate-x-9') : 'translate-x-0',
        ]"
        class="pointer-events-none inline-block transform rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 transition duration-200 ease-in-out"
      />
    </Switch>

    <span
      v-if="enabledText"
      :class="[
        enabled ? 'text-slate-900' : 'text-slate-400',
        'cursor-default select-none transition-colors duration-200',
      ]"
    >
      {{ enabledText }}
    </span>
  </div>
</template>
