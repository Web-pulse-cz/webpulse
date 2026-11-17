<script setup lang="ts">
import { ChevronDownIcon } from '@heroicons/vue/16/solid';

interface Tab {
  name: string;
  link: string;
  current?: boolean;
  // Počet chyb validace v záložce → červený odznak
  errors?: number;
}

const props = defineProps<{
  tabs: Tab[];
}>();

const router = useRouter();

const currentLink = computed(
  () => props.tabs.find((tab) => tab.current)?.link ?? props.tabs[0]?.link ?? '',
);

function onSelect(event: Event) {
  const link = (event.target as HTMLSelectElement).value;
  if (link) router.push(link);
}

function optionLabel(tab: Tab): string {
  return tab.errors ? `${tab.name} (${tab.errors})` : tab.name;
}
</script>

<template>
  <div class="py-2">
    <div class="grid grid-cols-1 sm:hidden">
      <select
        aria-label="Vyberte záložku"
        :value="currentLink"
        class="col-start-1 row-start-1 w-full appearance-none rounded-xl bg-white py-2 pl-3 pr-8 text-base text-slate-900 outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
        @change="onSelect"
      >
        <option v-for="(tab, index) in tabs" :key="index" :value="tab.link">
          {{ optionLabel(tab) }}
        </option>
      </select>
      <ChevronDownIcon
        class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end fill-slate-500"
        aria-hidden="true"
      />
    </div>
    <div class="hidden sm:block">
      <nav class="scrollbar-hide flex space-x-4 overflow-x-auto" aria-label="Záložky">
        <NuxtLink
          v-for="(tab, index) in tabs"
          :key="index"
          :to="tab.link"
          :class="[
            tab.current ? 'bg-indigo-100 text-indigo-700' : 'text-slate-500 hover:text-slate-700',
            'inline-flex shrink-0 items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
          ]"
          :aria-current="tab.current ? 'page' : undefined"
        >
          {{ tab.name }}
          <span
            v-if="tab.errors"
            class="inline-flex min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-xs font-semibold leading-none text-white"
            :aria-label="`${tab.errors} chyb`"
          >
            {{ tab.errors }}
          </span>
        </NuxtLink>
      </nav>
    </div>
  </div>
</template>

<style>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
