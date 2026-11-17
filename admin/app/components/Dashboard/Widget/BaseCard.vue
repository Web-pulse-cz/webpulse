<script setup lang="ts">
import { NuxtLink } from '#components';
import { colorClasses } from '~/components/Dashboard/widgets';

const props = defineProps<{
  title: string;
  icon: unknown;
  color: string;
  count?: number;
  link?: string;
}>();

// The whole card is not a single <a>: widgets render their own links/buttons inside
// the slot and nested anchors are invalid HTML. Instead the header is a real NuxtLink
// (keyboard, middle click, "open in new tab") and clicks on empty card space delegate to it.
function onCardClick(event: MouseEvent) {
  if (!props.link || event.defaultPrevented) return;
  const target = event.target as HTMLElement | null;
  if (target?.closest('a, button, input, select, textarea, label, [role="button"]')) return;
  if (window.getSelection()?.toString()) return;
  navigateTo(props.link);
}
</script>

<template>
  <div
    class="group flex h-full flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-100 transition"
    :class="
      link
        ? 'cursor-pointer focus-within:ring-slate-200 hover:-translate-y-0.5 hover:shadow-sm hover:ring-slate-200'
        : ''
    "
    @click="onCardClick"
  >
    <div class="mb-5 flex items-center justify-between">
      <component
        :is="link ? NuxtLink : 'div'"
        :to="link || undefined"
        class="flex items-center gap-3 rounded-lg"
        :class="
          link
            ? 'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2'
            : ''
        "
      >
        <div
          class="flex size-9 items-center justify-center rounded-lg"
          :class="[colorClasses[color]?.bg, colorClasses[color]?.text]"
        >
          <component :is="icon" class="size-5" aria-hidden="true" />
        </div>
        <LayoutTitle class="!mb-0">{{ title }}</LayoutTitle>
      </component>
      <div v-if="count !== undefined" class="label-caps flex items-baseline gap-1 text-slate-400">
        Celkem:
        <span class="text-sm/none font-black" :class="colorClasses[color]?.count">{{
          count || 0
        }}</span>
      </div>
    </div>

    <slot />
  </div>
</template>
