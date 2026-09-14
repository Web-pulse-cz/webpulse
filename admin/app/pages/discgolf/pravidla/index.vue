<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import {
  ChevronDownIcon,
  MagnifyingGlassIcon,
  ScaleIcon,
  BookOpenIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import rulesData from '~/data/discgolf-pravidla.json';
import RulesBlocks from '~/components/DiscGolf/RulesBlocks.vue';

interface RuleItem {
  marker: string;
  text: string;
  children?: RuleItem[];
}

interface RuleBlock {
  type: 'subheading' | 'paragraph' | 'list' | 'table';
  number?: string;
  title?: string;
  text?: string;
  items?: RuleItem[];
  rows?: string[][];
}

interface RuleSection {
  id: string;
  title: string;
  blocks: RuleBlock[];
}

const { highlightText } = useHighlight();

const pageTitle = ref('Pravidla');
const breadcrumbs = ref([{ name: pageTitle.value, link: '/discgolf/pravidla', current: true }]);

const allSections = rulesData.sections as RuleSection[];
const mainSections = allSections.filter((s) => /^\d/.test(s.id));
const appendixSections = allSections.filter((s) => !/^\d/.test(s.id));

const search = ref('');
const openSections = ref<Set<string>>(new Set(['800']));

function flattenBlocks(blocks: RuleBlock[]): string {
  const parts: string[] = [];
  const walkItems = (items: RuleItem[] = []) => {
    for (const item of items) {
      parts.push(item.text ?? '');
      if (item.children?.length) walkItems(item.children);
    }
  };
  for (const block of blocks) {
    if (block.type === 'subheading') parts.push(`${block.number} ${block.title}`);
    else if (block.type === 'paragraph') parts.push(block.text ?? '');
    else if (block.type === 'list') walkItems(block.items ?? []);
    else if (block.type === 'table') block.rows?.forEach((row) => parts.push(row.join(' ')));
  }
  return parts.join(' ').toLowerCase();
}

const searchIndex = new Map<string, string>(
  allSections.map((s) => [s.id, `${s.id} ${s.title} ${flattenBlocks(s.blocks)}`.toLowerCase()]),
);

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

function sectionMatches(section: RuleSection): boolean {
  if (!normalizedSearch.value) return true;
  return (searchIndex.get(section.id) ?? '').includes(normalizedSearch.value);
}

function isOpen(id: string): boolean {
  if (normalizedSearch.value) return sectionMatches(allSections.find((s) => s.id === id)!);
  return openSections.value.has(id);
}

function toggle(id: string) {
  if (openSections.value.has(id)) openSections.value.delete(id);
  else openSections.value.add(id);
  // force reactivity for Set mutation
  openSections.value = new Set(openSections.value);
}

function expandAll() {
  openSections.value = new Set(allSections.map((s) => s.id));
}

function collapseAll() {
  openSections.value = new Set();
}

function openSectionAndScroll(id: string) {
  if (!allSections.some((s) => s.id === id)) return;
  openSections.value = new Set(openSections.value).add(id);
  nextTick(() => {
    document
      .getElementById(`pravidlo-${id}`)
      ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

function onRuleTextClick(event: MouseEvent) {
  const link = (event.target as HTMLElement).closest('[data-ref-section]') as HTMLElement | null;
  if (!link) return;
  event.preventDefault();
  const id = link.dataset.refSection;
  if (id) openSectionAndScroll(id);
}

useHead({ title: pageTitle.value });
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6" @click="onRuleTextClick">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
            <ScaleIcon class="size-6 text-emerald-600" />
          </div>
          <div>
            <p class="text-sm font-extrabold text-slate-900">{{ rulesData.docTitle }}</p>
            <p class="mt-0.5 text-xs text-slate-500">{{ rulesData.docMeta?.[0] }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="rounded-xl px-3 py-2 text-xs font-bold text-slate-500 ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
            @click="collapseAll"
          >
            Sbalit vše
          </button>
          <button
            type="button"
            class="rounded-xl px-3 py-2 text-xs font-bold text-slate-500 ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
            @click="expandAll"
          >
            Rozbalit vše
          </button>
        </div>
      </div>

      <div class="relative mt-6">
        <MagnifyingGlassIcon
          class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
        />
        <input
          v-model="search"
          type="text"
          placeholder="Hledat v pravidlech…"
          class="block w-full rounded-xl border-0 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500"
        />
      </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
      <div
        class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400"
      >
        <BookOpenIcon class="size-4" />
        Pravidla hry (800–813)
      </div>

      <div class="space-y-3">
        <div
          v-for="section in mainSections"
          v-show="sectionMatches(section)"
          :id="`pravidlo-${section.id}`"
          :key="section.id"
          class="scroll-mt-4 overflow-hidden rounded-xl ring-1 ring-slate-100"
        >
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 bg-slate-50 px-4 py-3 text-left hover:bg-slate-100"
            @click="toggle(section.id)"
          >
            <span class="flex items-center gap-3">
              <span
                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-extrabold text-emerald-600 ring-1 ring-slate-200"
              >
                {{ section.id }}
              </span>
              <span
                class="text-sm font-bold text-slate-900"
                v-html="highlightText(section.title, search)"
              />
            </span>
            <ChevronDownIcon
              class="size-4 shrink-0 text-slate-400 transition-transform duration-200"
              :class="isOpen(section.id) ? 'rotate-180' : ''"
            />
          </button>
          <div v-if="isOpen(section.id)" class="border-t border-slate-100 px-4 py-4 sm:px-5">
            <RulesBlocks :blocks="section.blocks" :search="search" />
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
      <div
        class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400"
      >
        <BookOpenIcon class="size-4" />
        Přílohy
      </div>

      <div class="space-y-3">
        <div
          v-for="section in appendixSections"
          v-show="sectionMatches(section)"
          :id="`pravidlo-${section.id}`"
          :key="section.id"
          class="scroll-mt-4 overflow-hidden rounded-xl ring-1 ring-slate-100"
        >
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 bg-slate-50 px-4 py-3 text-left hover:bg-slate-100"
            @click="toggle(section.id)"
          >
            <span class="flex items-center gap-3">
              <span
                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-extrabold text-emerald-600 ring-1 ring-slate-200"
              >
                {{ section.id }}
              </span>
              <span
                class="text-sm font-bold text-slate-900"
                v-html="highlightText(section.title, search)"
              />
            </span>
            <ChevronDownIcon
              class="size-4 shrink-0 text-slate-400 transition-transform duration-200"
              :class="isOpen(section.id) ? 'rotate-180' : ''"
            />
          </button>
          <div v-if="isOpen(section.id)" class="border-t border-slate-100 px-4 py-4 sm:px-5">
            <RulesBlocks :blocks="section.blocks" :search="search" />
          </div>
        </div>
      </div>
    </div>

    <div
      class="flex items-start gap-3 rounded-2xl bg-amber-50 p-5 text-xs leading-relaxed text-amber-800 ring-1 ring-amber-100"
    >
      <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-amber-500" />
      <p>{{ rulesData.closingNote }}</p>
    </div>
  </div>
</template>
