<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps<{
	widgetKey: string;
	title: string;
	icon: unknown;
	endpoint: string;
	link: string;
	color: string;
	emptyLabel?: string;
}>();

const total = ref(0);
const items = ref<Array<{ id: number; name: string | null; updated_at: string }>>([]);
const loading = ref(false);

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();

	try {
		const response: any = await client(props.endpoint, {
			method: 'GET',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			query: {
				paginate: 5,
				page: 1,
				orderBy: 'updated_at',
				orderWay: 'desc',
			},
		});
		total.value = response?.total ?? 0;
		items.value = response?.data ?? [];
	} catch (_) {
		total.value = 0;
		items.value = [];
	} finally {
		loading.value = false;
	}
}

onMounted(loadItems);
</script>

<template>
	<DashboardWidgetBaseCard :title="title" :icon="icon" :color="color" :count="total" :link="link">
		<div v-if="loading" class="space-y-2">
			<div
				v-for="n in 3"
				:key="n"
				class="h-9 animate-pulse rounded-lg border border-slate-100 bg-slate-50"
			/>
		</div>
		<div v-else-if="items.length" class="space-y-2">
			<div
				v-for="item in items"
				:key="item.id"
				class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-700 transition group-hover:border-slate-200"
			>
				<span class="truncate">{{ item.name || '— bez názvu —' }}</span>
				<span class="shrink-0 text-[10px] uppercase tracking-widest text-slate-400">
					{{ new Date(item.updated_at).toLocaleDateString('cs-CZ') }}
				</span>
			</div>
		</div>
		<div
			v-else
			class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-xs text-slate-400"
		>
			{{ emptyLabel || 'Zatím žádné položky' }}
		</div>
	</DashboardWidgetBaseCard>
</template>
