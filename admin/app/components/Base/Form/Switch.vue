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
});
</script>

<template>
  <div class="flex w-full items-center justify-between text-sm text-grayCustom lg:w-auto">
    <span>{{ disabledText }}</span>
    <Switch
      v-model="enabled"
      :class="enabled ? 'bg-success' : 'bg-warning'"
      class="relative mx-4 inline-flex h-[28px] w-[64px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
    >
      <span
        aria-hidden="true"
        :class="enabled ? 'translate-x-9' : 'translate-x-0'"
        class="pointer-events-none inline-block h-[24px] w-[24px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
      />
    </Switch>
    <span>{{ enabledText }}</span>
  </div>
</template>
