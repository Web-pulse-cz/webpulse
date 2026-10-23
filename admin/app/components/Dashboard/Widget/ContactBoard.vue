<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';

const props = defineProps<{
  widgetKey: string;
  title: string;
  icon: unknown;
  endpoint: string;
  link: string;
  color: string;
}>();

const loading = ref(false);
const sections = ref([] as any[]);

const totalCards = computed(() =>
  sections.value.reduce((acc, section) => acc + (section.cards?.length || 0), 0),
);

const priorityLabels: Record<string, string> = {
  low: 'Nízká',
  medium: 'Střední',
  high: 'Vysoká',
  critical: 'Kritická',
};

const priorityColors: Record<string, string> = {
  critical: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  medium: 'bg-slate-100 text-slate-600',
  low: 'bg-blue-100 text-blue-600',
};

const sectionColorClasses: Record<string, { bg: string; dot: string }> = {
  red: { bg: 'bg-red-50', dot: 'bg-red-500' },
  orange: { bg: 'bg-orange-50', dot: 'bg-orange-500' },
  yellow: { bg: 'bg-yellow-50', dot: 'bg-yellow-500' },
  lime: { bg: 'bg-lime-50', dot: 'bg-lime-500' },
  green: { bg: 'bg-green-50', dot: 'bg-green-500' },
  emerald: { bg: 'bg-emerald-50', dot: 'bg-emerald-500' },
  teal: { bg: 'bg-teal-50', dot: 'bg-teal-500' },
  cyan: { bg: 'bg-cyan-50', dot: 'bg-cyan-500' },
  sky: { bg: 'bg-sky-50', dot: 'bg-sky-500' },
  blue: { bg: 'bg-blue-50', dot: 'bg-blue-500' },
  indigo: { bg: 'bg-indigo-50', dot: 'bg-indigo-500' },
  violet: { bg: 'bg-violet-50', dot: 'bg-violet-500' },
  purple: { bg: 'bg-purple-50', dot: 'bg-purple-500' },
  fuchsia: { bg: 'bg-fuchsia-50', dot: 'bg-fuchsia-500' },
  pink: { bg: 'bg-pink-50', dot: 'bg-pink-500' },
  rose: { bg: 'bg-rose-50', dot: 'bg-rose-500' },
  slate: { bg: 'bg-slate-100', dot: 'bg-slate-500' },
  gray: { bg: 'bg-gray-100', dot: 'bg-gray-500' },
  zinc: { bg: 'bg-zinc-100', dot: 'bg-zinc-500' },
  stone: { bg: 'bg-stone-100', dot: 'bg-stone-500' },
  neutral: { bg: 'bg-neutral-100', dot: 'bg-neutral-500' },
};

function sectionColorClass(color: string, variant: 'bg' | 'dot') {
  return (sectionColorClasses[color] || sectionColorClasses.slate)[variant];
}

async function loadSections() {
  loading.value = true;
  const client = useSanctumClient();
  await client(props.endpoint, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r: any) => {
      sections.value = r;
    })
    .catch(() => {
      sections.value = [];
    })
    .finally(() => {
      loading.value = false;
    });
}

onMounted(loadSections);
</script>

<template>
  <DashboardWidgetBaseCard
    :title="title"
    :icon="icon"
    :color="color"
    :count="totalCards"
    :link="link"
  >
    <div v-if="loading" class="py-10 text-center text-sm text-slate-400">Načítání...</div>
    <div v-else-if="!sections.length" class="py-10 text-center text-sm text-slate-400">
      Zatím nejsou vytvořené žádné sekce.
    </div>
    <div v-else class="flex items-start gap-3 overflow-x-auto pb-1">
      <div
        v-for="section in sections"
        :key="section.id"
        class="w-56 shrink-0 rounded-xl p-2.5"
        :class="sectionColorClass(section.color, 'bg')"
      >
        <div class="mb-2 flex items-center justify-between gap-2 px-0.5">
          <div class="flex min-w-0 items-center gap-1.5">
            <span
              class="size-2 shrink-0 rounded-full"
              :class="sectionColorClass(section.color, 'dot')"
            />
            <span class="truncate text-xs font-bold text-slate-700">{{ section.name }}</span>
          </div>
          <span class="shrink-0 text-[10px] font-bold text-slate-400">{{
            section.cards?.length || 0
          }}</span>
        </div>

        <div class="space-y-1.5">
          <NuxtLink
            v-for="card in section.cards"
            :key="card.id"
            :to="link"
            class="block rounded-lg bg-white p-2 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
          >
            <div class="flex items-start justify-between gap-1.5">
              <span class="truncate text-xs font-semibold text-slate-900">{{ card.title }}</span>
              <span
                class="shrink-0 rounded-full px-1.5 py-0.5 text-[8px] font-bold"
                :class="priorityColors[card.priority]"
                >{{ priorityLabels[card.priority] || card.priority }}</span
              >
            </div>
            <span class="mt-0.5 block truncate text-[10px] text-slate-400">{{
              card.contact_name || 'Bez kontaktu'
            }}</span>
          </NuxtLink>
          <p v-if="!section.cards?.length" class="py-2 text-center text-[10px] text-slate-300">
            Prázdné
          </p>
        </div>
      </div>
    </div>
  </DashboardWidgetBaseCard>
</template>
