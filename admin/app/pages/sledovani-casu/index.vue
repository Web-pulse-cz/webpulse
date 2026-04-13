<script setup lang="ts">
import { ref, inject, computed } from 'vue';
import { definePageMeta } from '#imports';
import {
	TrashIcon, PlayIcon, StopIcon, ArrowPathIcon,
	FunnelIcon, DocumentArrowDownIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const { formatNumber, formatDate, formatSeconds } = useFormat();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();

const pageTitle = ref('Sledování času');
const loading = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/sledovani-casu', current: true }]);

const tabs = ref([
	{ name: 'Přehled', link: '#prehled', current: false },
	{ name: 'Filtry a export', link: '#filtry', current: false },
]);

// Data
const entries = ref([]);
const projects = ref([]);
const tasks = ref([]);
const users = ref([]);
const totalPages = ref(1);
const currentPage = ref(1);

// Timer
const timerInterval = ref(null as any);
const timerDisplay = ref('00:00:00');
const runningEntry = ref(null as any);
const timerDescription = ref('');
const timerProjectId = ref(null as number | null);
const timerTaskId = ref(null as number | null);

// Filters
const filters = ref({
	date_from: '', date_to: '',
	project_id: null as number | null,
	task_id: null as number | null,
	user_id: null as number | null,
	search: '',
});

// ─── Grouped entries ───────────────────────────────────────

const groupedEntries = computed(() => {
	const groups: Record<string, { date: string; dateFormatted: string; items: Record<string, any> }> = {};

	for (const entry of entries.value as any[]) {
		const date = entry.date || 'unknown';
		if (!groups[date]) {
			groups[date] = { date, dateFormatted: formatDate(date), items: {} };
		}
		const key = (entry.description || '—') + '|' + (entry.project_id || '') + '|' + (entry.task_id || '');
		if (!groups[date].items[key]) {
			groups[date].items[key] = {
				description: entry.description || '—',
				entries: [],
				totalSeconds: 0,
				totalPriceWithoutVat: 0,
				totalVat: 0,
				totalPriceWithVat: 0,
				currency_symbol: entry.currency_symbol || 'Kč',
				project_name: entry.project_name,
				task_code: entry.task_code,
			};
		}
		groups[date].items[key].entries.push(entry);
		groups[date].items[key].totalSeconds += parseInt(entry.seconds) || 0;
		groups[date].items[key].totalPriceWithoutVat += parseFloat(entry.price_without_vat) || 0;
		groups[date].items[key].totalVat += parseFloat(entry.vat) || 0;
		groups[date].items[key].totalPriceWithVat += parseFloat(entry.price_with_vat) || 0;
	}

	return Object.values(groups).sort((a, b) => b.date.localeCompare(a.date));
});

// ─── Loaders ───────────────────────────────────────────────

async function loadEntries(page = 1) {
	loading.value = true;
	currentPage.value = page;
	const client = useSanctumClient();
	const query: Record<string, any> = { paginate: 50, page };

	if (tabs.value[1].current) {
		if (filters.value.date_from) query.date_from = filters.value.date_from;
		if (filters.value.date_to) query.date_to = filters.value.date_to;
		if (filters.value.project_id) query.project_id = filters.value.project_id;
		if (filters.value.task_id) query.task_id = filters.value.task_id;
		if (filters.value.user_id) query.user_id = filters.value.user_id;
		if (filters.value.search) query.search = filters.value.search;
	}

	await client('/api/admin/time-entry', {
		method: 'GET', query,
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		entries.value = r.data || r;
		totalPages.value = r.lastPage || 1;
	}).catch(() => {})
	.finally(() => { loading.value = false; });
}

async function loadProjects() {
	const client = useSanctumClient();
	await client('/api/admin/project', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		const d = r?.data || r;
		projects.value = d.map((p: any) => ({ value: p.id, name: p.name }));
	}).catch(() => {});
}

async function loadTasks() {
	const client = useSanctumClient();
	await client('/api/admin/task', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		const d = r?.data || r;
		tasks.value = d.map((t: any) => ({ value: t.id, name: (t.code ? t.code + ' ' : '') + t.name }));
	}).catch(() => {});
}

async function loadUsers() {
	const client = useSanctumClient();
	await client('/api/admin/user', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		const d = r?.data || r;
		users.value = d.map((u: any) => ({ value: u.id, name: u.name || u.email }));
	}).catch(() => {});
}

async function loadRunningTimer() {
	const client = useSanctumClient();
	try {
		const r = await client('/api/admin/time-entry/running', {
			method: 'GET',
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		});
		if (r && r.id && r.is_running && r.timer_started_at) {
			runningEntry.value = r;
			timerDescription.value = r.description || '';
			timerProjectId.value = r.project_id;
			timerTaskId.value = r.task_id;
			startTimerDisplay(r.timer_started_at);
		} else {
			runningEntry.value = null;
			timerDisplay.value = '00:00:00';
		}
	} catch {
		runningEntry.value = null;
	}
}

// ─── Timer ─────────────────────────────────────────────────

async function startTimer() {
	if (!timerDescription.value && !timerTaskId.value) {
		$toast.show({ summary: 'Info', detail: 'Zadejte popis nebo vyberte úkol.', severity: 'warning' });
		return;
	}
	const client = useSanctumClient();
	await client('/api/admin/time-entry/timer/start', {
		method: 'POST',
		body: JSON.stringify({
			project_id: timerProjectId.value,
			task_id: timerTaskId.value,
			description: timerDescription.value,
		}),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then(() => {
		loadRunningTimer();
	}).catch(() => {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se spustit timer.', severity: 'error' });
	});
}

async function stopTimer() {
	if (!runningEntry.value?.id) return;
	const entryId = runningEntry.value.id;
	const client = useSanctumClient();
	try {
		await client('/api/admin/time-entry/timer/' + entryId + '/stop', {
			method: 'POST',
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		});
		clearInterval(timerInterval.value);
		timerInterval.value = null;
		runningEntry.value = null;
		timerDisplay.value = '00:00:00';
		timerDescription.value = '';
		timerProjectId.value = null;
		timerTaskId.value = null;
		loadEntries();
	} catch {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se zastavit timer.', severity: 'error' });
	}
}

function startTimerDisplay(startedAt: string) {
	if (!startedAt) { timerDisplay.value = '00:00:00'; return; }
	clearInterval(timerInterval.value);
	const startTime = new Date(startedAt).getTime();
	if (isNaN(startTime)) { timerDisplay.value = '00:00:00'; return; }

	timerInterval.value = setInterval(() => {
		const elapsed = Math.floor((Date.now() - startTime) / 1000);
		if (elapsed < 0) { timerDisplay.value = '00:00:00'; return; }
		const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
		const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
		const s = String(elapsed % 60).padStart(2, '0');
		timerDisplay.value = `${h}:${m}:${s}`;
	}, 1000);
}

function restartEntry(entry: any) {
	timerDescription.value = entry.description || '';
	timerProjectId.value = entry.project_id || null;
	timerTaskId.value = entry.task_id || null;
	startTimer();
}

async function deleteEntry(id: number) {
	const client = useSanctumClient();
	await client('/api/admin/time-entry/' + id, {
		method: 'DELETE', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => { loadEntries(); });
}

// ─── Export ────────────────────────────────────────────────

async function exportPdf() {
	const client = useSanctumClient();
	const params = new URLSearchParams();
	if (filters.value.date_from) params.set('date_from', filters.value.date_from);
	if (filters.value.date_to) params.set('date_to', filters.value.date_to);
	if (filters.value.project_id) params.set('project_id', String(filters.value.project_id));
	if (filters.value.task_id) params.set('task_id', String(filters.value.task_id));
	if (filters.value.user_id) params.set('user_id', String(filters.value.user_id));
	if (filters.value.search) params.set('search', filters.value.search);
	try {
		const res = await client.raw('/api/admin/time-entry/export-pdf?' + params.toString(), {
			method: 'GET', credentials: 'include', responseType: 'blob',
		});
		if (!res.ok) throw new Error('Chyba');
		const blob = res._data as Blob;
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = 'casove-zaznamy-' + new Date().toISOString().split('T')[0] + '.pdf';
		document.body.appendChild(a);
		a.click();
		a.remove();
		URL.revokeObjectURL(url);
	} catch {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se exportovat PDF.', severity: 'error' });
	}
}

// ─── Lifecycle ─────────────────────────────────────────────

watchEffect(() => {
	const h = route.hash;
	if (h) tabs.value.forEach((t) => { t.current = t.link === h; });
	else { tabs.value[0].current = true; router.push(route.path + '#prehled'); }
});

watch(selectedSiteHash, () => { loadEntries(); loadProjects(); loadTasks(); });
onBeforeUnmount(() => { clearInterval(timerInterval.value); });

useHead({ title: pageTitle.value });
onMounted(() => {
	loadEntries();
	loadProjects();
	loadTasks();
	loadUsers();
	loadRunningTimer();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="project_time_entries" />

		<!-- Timer bar (always visible) -->
		<div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-5">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-center">
				<div class="flex flex-1 flex-col gap-3 sm:flex-row">
					<BaseFormInput
						v-model="timerDescription"
						label=""
						name="timer_desc"
						:placeholder="timerTaskId ? '' : 'Na čem pracuješ?'"
						class="flex-1"
						:disabled="!!runningEntry"
					/>
					<BaseFormSelect
						v-model="timerTaskId"
						label=""
						name="timer_task"
						:options="[{ value: null, name: '— Úkol —' }, ...tasks]"
						class="w-full sm:w-44"
						:disabled="!!runningEntry"
					/>
					<BaseFormSelect
						v-model="timerProjectId"
						label=""
						name="timer_project"
						:options="[{ value: null, name: '— Projekt —' }, ...projects]"
						class="w-full sm:w-44"
						:disabled="!!runningEntry"
					/>
				</div>

				<div class="flex items-center justify-between gap-4 sm:justify-end">
					<span
						class="font-mono text-2xl font-bold tabular-nums sm:text-3xl"
						:class="runningEntry ? 'text-indigo-600' : 'text-slate-300'"
					>
						{{ timerDisplay }}
					</span>
					<button
						v-if="!runningEntry"
						type="button"
						class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
						@click="startTimer"
					>
						<PlayIcon class="size-5" /> Start
					</button>
					<button
						v-else
						type="button"
						class="flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-red-500"
						@click="stopTimer"
					>
						<StopIcon class="size-5" /> Stop
					</button>
				</div>
			</div>
		</div>

		<LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

		<!-- ═══ Tab: Přehled ═══ -->
		<template v-if="tabs.find((t) => t.current && t.link === '#prehled')">
			<div v-if="!groupedEntries.length && !loading" class="py-16 text-center text-sm text-slate-400">
				Zatím žádné záznamy. Spusťte timer a začněte trackovat.
			</div>

			<div class="space-y-6">
				<div v-for="dayGroup in groupedEntries" :key="dayGroup.date">
					<div class="flex items-center gap-3 px-1">
						<span class="text-sm font-bold text-slate-900">{{ dayGroup.dateFormatted }}</span>
						<div class="h-px flex-1 bg-slate-200"></div>
					</div>

					<div class="mt-2 space-y-2">
						<div
							v-for="(group, groupKey) in dayGroup.items"
							:key="groupKey"
							class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200"
						>
							<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
								<div class="flex flex-wrap items-center gap-2">
									<span class="text-sm font-medium text-slate-900">{{ group.description }}</span>
									<span v-if="group.task_code" class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-mono font-bold text-indigo-600">{{ group.task_code }}</span>
									<span v-if="group.project_name" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">{{ group.project_name }}</span>
								</div>
								<div class="flex flex-wrap items-center gap-3">
									<span class="font-mono text-sm font-bold tabular-nums text-slate-900">{{ formatSeconds(group.totalSeconds) }}</span>
									<span v-if="group.totalPriceWithoutVat > 0" class="text-xs text-slate-500 tabular-nums">
										{{ formatNumber(group.totalPriceWithoutVat) }} {{ group.currency_symbol }}
										<span v-if="group.totalVat > 0" class="text-slate-400"> + {{ formatNumber(group.totalVat) }} DPH = <span class="font-medium text-slate-700">{{ formatNumber(group.totalPriceWithVat) }} {{ group.currency_symbol }}</span></span>
									</span>
									<button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600" title="Spustit znovu" @click="restartEntry(group.entries[0])">
										<ArrowPathIcon class="size-4" />
									</button>
									<button type="button" class="rounded-lg p-1.5 text-slate-300 transition hover:bg-red-50 hover:text-red-500" title="Smazat" @click="deleteEntry(group.entries[0].id)">
										<TrashIcon class="size-4" />
									</button>
								</div>
							</div>

							<div v-if="group.entries.length > 1" class="mt-2 border-t border-slate-100 pt-2">
								<div v-for="entry in group.entries" :key="entry.id" class="flex items-center justify-between py-1 text-xs text-slate-500">
									<span>{{ formatSeconds(entry.seconds) }}</span>
									<span>{{ entry.user_name }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div v-if="totalPages > 1" class="flex items-center justify-center gap-2 pt-4">
				<button v-for="p in totalPages" :key="p" type="button" class="rounded-lg px-3 py-1.5 text-sm font-medium transition" :class="currentPage === p ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'" @click="loadEntries(p)">{{ p }}</button>
			</div>
		</template>

		<!-- ═══ Tab: Filtry a export ═══ -->
		<template v-if="tabs.find((t) => t.current && t.link === '#filtry')">
			<div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
				<div class="mb-4 flex items-center gap-3">
					<FunnelIcon class="size-5 text-indigo-600" />
					<span class="text-sm font-bold text-slate-900">Filtry</span>
				</div>
				<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
					<BaseFormInput v-model="filters.date_from" label="Od" type="date" name="f_from" />
					<BaseFormInput v-model="filters.date_to" label="Do" type="date" name="f_to" />
					<BaseFormSelect v-model="filters.project_id" label="Projekt" name="f_project" :options="[{ value: null, name: 'Všechny' }, ...projects]" />
					<BaseFormSelect v-model="filters.task_id" label="Úkol" name="f_task" :options="[{ value: null, name: 'Všechny' }, ...tasks]" />
					<BaseFormSelect v-model="filters.user_id" label="Uživatel" name="f_user" :options="[{ value: null, name: 'Všichni' }, ...users]" />
					<BaseFormInput v-model="filters.search" label="Popis" name="f_search" placeholder="Hledat..." />
				</div>
				<div class="mt-4 flex flex-wrap gap-3">
					<button type="button" class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500" @click="loadEntries()">
						<FunnelIcon class="size-4" /> Filtrovat
					</button>
					<button type="button" class="flex items-center gap-2 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600" @click="exportPdf">
						<DocumentArrowDownIcon class="size-4" /> Export PDF
					</button>
				</div>
			</div>

			<div v-if="!groupedEntries.length && !loading" class="py-12 text-center text-sm text-slate-400">
				Žádné záznamy odpovídající filtru.
			</div>

			<div class="space-y-6">
				<div v-for="dayGroup in groupedEntries" :key="dayGroup.date">
					<div class="flex items-center gap-3 px-1">
						<span class="text-sm font-bold text-slate-900">{{ dayGroup.dateFormatted }}</span>
						<div class="h-px flex-1 bg-slate-200"></div>
					</div>
					<div class="mt-2 space-y-2">
						<div v-for="(group, groupKey) in dayGroup.items" :key="groupKey" class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
							<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
								<div class="flex flex-wrap items-center gap-2">
									<span class="text-sm font-medium text-slate-900">{{ group.description }}</span>
									<span v-if="group.task_code" class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-mono font-bold text-indigo-600">{{ group.task_code }}</span>
									<span v-if="group.project_name" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">{{ group.project_name }}</span>
								</div>
								<div class="flex flex-wrap items-center gap-2">
									<span class="font-mono text-sm font-bold tabular-nums text-slate-900">{{ formatSeconds(group.totalSeconds) }}</span>
									<span v-if="group.totalPriceWithoutVat > 0" class="text-xs text-slate-500 tabular-nums">
										{{ formatNumber(group.totalPriceWithoutVat) }} {{ group.currency_symbol }}
										<span v-if="group.totalVat > 0" class="text-slate-400"> + {{ formatNumber(group.totalVat) }} DPH = <span class="font-medium text-slate-700">{{ formatNumber(group.totalPriceWithVat) }} {{ group.currency_symbol }}</span></span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div v-if="totalPages > 1" class="flex items-center justify-center gap-2 pt-4">
				<button v-for="p in totalPages" :key="p" type="button" class="rounded-lg px-3 py-1.5 text-sm font-medium transition" :class="currentPage === p ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'" @click="loadEntries(p)">{{ p }}</button>
			</div>
		</template>
	</div>
</template>
