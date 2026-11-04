<script setup lang="ts">
import { ref, onMounted } from 'vue';

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
  <DashboardWidgetBaseCard :title="title" :icon="icon" color="violet" :count="changelog.length">
    <div v-if="loading" class="py-10 text-center text-sm text-slate-400">Načítání...</div>
    <div v-else-if="!changelog.length" class="py-10 text-center text-sm text-slate-400">
      Zatím žádné záznamy.
    </div>
    <div v-else class="dashboard-changelog-scroll max-h-[520px] space-y-3 overflow-y-auto pr-1">
      <ChangelogCard
        v-for="changelogItem in changelog"
        :key="changelogItem.id"
        :changelog="changelogItem"
      />
    </div>
  </DashboardWidgetBaseCard>
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
