<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { RocketLaunchIcon } from '@heroicons/vue/24/outline';

defineProps<{
  widgetKey: string;
  title: string;
  icon: unknown;
}>();

const changelog = ref<any[]>([]);
const loading = ref(false);

async function loadChangelog() {
  loading.value = true;
  const client = useSanctumClient();

  try {
    const response: any = await client('/api/admin/changelog', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      query: {
        orderBy: 'id',
        orderWay: 'desc',
      },
    });
    changelog.value = Array.isArray(response) ? response : (response?.data ?? []);
  } catch (_) {
    changelog.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadChangelog);
</script>

<template>
  <div class="h-full rounded-2xl bg-white px-6 py-6 ring-1 ring-slate-100">
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <RocketLaunchIcon class="size-4 text-slate-400" />
        <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest text-slate-400"
          >Changelog</LayoutTitle
        >
      </div>
      <span
        class="rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-white"
      >
        v1.0.6
      </span>
    </div>

    <div v-if="loading" class="space-y-3">
      <div
        v-for="n in 2"
        :key="n"
        class="h-24 animate-pulse rounded-lg border border-slate-100 bg-slate-50"
      />
    </div>
    <div
      v-else-if="changelog.length"
      class="dashboard-changelog-scroll max-h-[520px] space-y-6 overflow-y-auto pb-2 pr-2"
    >
      <ChangelogCard
        v-for="(changelogItem, index) in changelog"
        :key="index"
        :changelog="changelogItem"
      />
    </div>
    <div
      v-else
      class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-xs text-slate-400"
    >
      Zatím žádné záznamy
    </div>
  </div>
</template>

<style scoped>
.dashboard-changelog-scroll::-webkit-scrollbar {
  width: 4px;
}
.dashboard-changelog-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.dashboard-changelog-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.dashboard-changelog-scroll::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
