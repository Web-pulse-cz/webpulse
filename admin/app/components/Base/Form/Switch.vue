<script setup lang="ts">
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue';

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
  // Přístupný název přepínače (aria-label); bez něj se použijí texty enabledText/disabledText
  label: {
    type: String,
    required: false,
    default: '',
  },
});
</script>

<template>
  <SwitchGroup
    as="div"
    class="flex w-full items-center justify-between text-sm font-medium sm:w-auto sm:justify-start"
  >
    <SwitchLabel
      v-if="disabledText"
      :passive="!!label"
      :class="[
        enabled ? 'text-slate-400' : 'text-slate-900',
        'cursor-pointer select-none transition-colors duration-200',
      ]"
    >
      {{ disabledText }}
    </SwitchLabel>

    <Switch
      v-model="enabled"
      :aria-label="label || undefined"
      :class="[
        enabled ? 'bg-indigo-600' : 'bg-slate-200',
        size === 'sm' ? 'h-6 w-11' : 'h-7 w-16',
        { 'ml-3': disabledText, 'mr-3': enabledText },
      ]"
      class="relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
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

    <SwitchLabel
      v-if="enabledText"
      :passive="!!label"
      :class="[
        enabled ? 'text-slate-900' : 'text-slate-400',
        'cursor-pointer select-none transition-colors duration-200',
      ]"
    >
      {{ enabledText }}
    </SwitchLabel>
  </SwitchGroup>
</template>
