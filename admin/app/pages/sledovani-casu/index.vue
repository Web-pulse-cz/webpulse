<script setup lang="ts">
import { ref } from 'vue';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Sledování času');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/sledovani-casu', current: true }]);

const items = ref([]);
const totalHours = ref(0);
const projects = ref([]);
const users = ref([]);

const filters = ref({
	date_from: '',
	date_to: '',
	project_id: null as number | null,
	user_id: null as number | null,
	search: '',
	paginate: 20,
	page: 1,
});

// Timer
const timerInterval = ref(null as any);
const timerDisplay = ref('00:00:00');
const runningEntry = ref(null as any);
const timerProjectId = ref(null as number | null);
const timerDescription = ref('');

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();

	const query: Record<string, any> = { paginate: filters.value.paginate, page: filters.value.page };
	if (filters.value.date_from) query.date_from = filters.value.date_from;
	if (filters.value.date_to) query.date_to = filters.value.date_to;
	if (filters.value.project_id) query.project_id = filters.value.project_id;
	if (filters.value.user_id) query.user_id = filters.value.user_id;
	if (filters.value.search) query.search = filters.value.search;

	await client('/api/admin/time-entry', {
		method: 'GET', query,
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((response) => {
		items.value = response.data || response;
		totalHours.value = response.total_hours || 0;
	}).catch(() => {
		error.value = true;
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst záznamy.', severity: 'error' });
	}).finally(() => { loading.value = false; });
}

async function loadProjects() {
	const client = useSanctumClient();
	await client('/api/admin/project', { method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' } })
		.then((r) => { const d = r?.data || r; projects.value = d.map((p: any) => ({ value: p.id, name: p.name })); }).catch(() => {});
}

async function loadUsers() {
	const client = useSanctumClient();
	await client('/api/admin/user', { method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' } })
		.then((r) => { const d = r?.data || r; users.value = d.map((u: any) => ({ value: u.id, name: u.name || u.email })); }).catch(() => {});
}

async function loadRunningTimer() {
	const client = useSanctumClient();
	await client('/api/admin/time-entry/running', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		if (r) {
			runningEntry.value = r;
			startTimerDisplay(r.timer_started_at);
		}
	}).catch(() => {});
}

async function deleteEntry(id: number) {
	const client = useSanctumClient();
	await client('/api/admin/time-entry/' + id, {
		method: 'DELETE', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => { loadItems(); });
}

async function startTimer() {
	const client = useSanctumClient();
	await client('/api/admin/time-entry/timer/start', {
		method: 'POST',
		body: JSON.stringify({ project_id: timerProjectId.value, description: timerDescription.value }),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => { loadRunningTimer(); loadItems(); });
}

async function stopTimer() {
	if (!runningEntry.value) return;
	const client = useSanctumClient();
	await client('/api/admin/time-entry/timer/' + runningEntry.value.id + '/stop', {
		method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => {
		clearInterval(timerInterval.value);
		runningEntry.value = null;
		timerDisplay.value = '00:00:00';
		loadItems();
	});
}

function startTimerDisplay(startedAt: string) {
	clearInterval(timerInterval.value);
	timerInterval.value = setInterval(() => {
		const e = Math.floor((Date.now() - new Date(startedAt).getTime()) / 1000);
		timerDisplay.value = `${String(Math.floor(e / 3600)).padStart(2, '0')}:${String(Math.floor((e % 3600) / 60)).padStart(2, '0')}:${String(e % 60).padStart(2, '0')}`;
	}, 1000);
}

function exportPdf() {
	const params = new URLSearchParams();
	if (filters.value.date_from) params.set('date_from', filters.value.date_from);
	if (filters.value.date_to) params.set('date_to', filters.value.date_to);
	if (filters.value.project_id) params.set('project_id', String(filters.value.project_id));
	if (filters.value.user_id) params.set('user_id', String(filters.value.user_id));
	if (filters.value.search) params.set('search', filters.value.search);
	window.open('/api/admin/time-entry/export-pdf?' + params.toString(), '_blank');
}

function applyFilters() {
	filters.value.page = 1;
	loadItems();
}

function resetFilters() {
	filters.value = { date_from: '', date_to: '', project_id: null, user_id: null, search: '', paginate: 20, page: 1 };
	loadItems();
}

onBeforeUnmount(() => { clearInterval(timerInterval.value); });

useHead({ title: pageTitle.value });

onMounted(() => {
	loadItems();
	loadProjects();
	loadUsers();
	loadRunningTimer();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="project_time_entries" />

		<!-- Timer -->
		<LayoutContainer>
			<div class="flex items-center justify-between">
				<div class="flex items-center gap-4">
					<span class="font-mono text-3xl font-bold" :class="runningEntry ? 'text-indigo-600' : 'text-slate-300'">{{ timerDisplay }}</span>
					<div v-if="runningEntry" class="text-sm text-slate-500">
						<span v-if="runningEntry.project_name" class="font-medium">{{ runningEntry.project_name }}</span>
						<span v-if="runningEntry.description" class="ml-2">{{ runningEntry.description }}</span>
					</div>
				</div>
				<div class="flex items-center gap-3">
					<template v-if="!runningEntry">
						<BaseFormSelect v-model="timerProjectId" label="" name="timer_project" :options="projects" class="w-48" />
						<BaseFormInput v-model="timerDescription" label="" name="timer_desc" placeholder="Popis..." class="w-48" />
						<button type="button" class="rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-500 transition" @click="startTimer">Start</button>
					</template>
					<button v-else type="button" class="rounded-lg bg-red-600 px-6 py-3 text-sm font-medium text-white hover:bg-red-500 transition" @click="stopTimer">Stop</button>
				</div>
			</div>
		</LayoutContainer>

		<!-- Filters -->
		<LayoutContainer class="!py-4">
			<div class="flex flex-wrap items-end gap-3">
				<BaseFormInput v-model="filters.date_from" label="Od" type="date" name="date_from" class="w-40" />
				<BaseFormInput v-model="filters.date_to" label="Do" type="date" name="date_to" class="w-40" />
				<BaseFormSelect v-model="filters.project_id" label="Projekt" name="f_project" :options="projects" class="w-48" />
				<BaseFormSelect v-model="filters.user_id" label="Uživatel" name="f_user" :options="users" class="w-48" />
				<BaseFormInput v-model="filters.search" label="Popis" name="f_search" placeholder="Hledat..." class="w-48" />
				<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500" @click="applyFilters">Filtrovat</button>
				<button type="button" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-300" @click="resetFilters">Reset</button>
				<button type="button" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600" @click="exportPdf">Export PDF</button>
			</div>
		</LayoutContainer>

		<!-- Summary -->
		<div class="flex gap-4">
			<div class="rounded-xl bg-indigo-50 px-6 py-4 ring-1 ring-indigo-200">
				<div class="text-2xl font-bold text-indigo-700">{{ Number(totalHours).toFixed(2) }} h</div>
				<div class="text-xs text-indigo-600">Celkem hodin (filtr)</div>
			</div>
		</div>

		<!-- Table -->
		<LayoutContainer>
			<div v-if="!items.length && !loading" class="py-12 text-center text-sm text-slate-400">Žádné záznamy odpovídající filtru.</div>
			<table v-else class="w-full text-sm">
				<thead>
					<tr class="border-b border-slate-200 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
						<th class="py-3 px-2">Datum</th>
						<th class="py-3 px-2">Projekt</th>
						<th class="py-3 px-2">Úkol</th>
						<th class="py-3 px-2">Uživatel</th>
						<th class="py-3 px-2">Popis</th>
						<th class="py-3 px-2 text-right">Hodiny</th>
						<th class="py-3 px-2 text-right">Sazba</th>
						<th class="py-3 px-2"></th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100">
					<tr v-for="entry in items" :key="entry.id" class="hover:bg-slate-50 transition">
						<td class="py-3 px-2 text-slate-600">{{ entry.date }}</td>
						<td class="py-3 px-2">{{ entry.project_name || '—' }}</td>
						<td class="py-3 px-2">
							<span v-if="entry.task_code" class="font-mono text-xs text-indigo-500">{{ entry.task_code }}</span>
							<span v-if="entry.task_name" class="ml-1">{{ entry.task_name }}</span>
							<span v-if="!entry.task_code && !entry.task_name">—</span>
						</td>
						<td class="py-3 px-2 text-slate-500">{{ entry.user_name || '—' }}</td>
						<td class="py-3 px-2 text-slate-500">{{ entry.description || '—' }}</td>
						<td class="py-3 px-2 text-right font-medium">
							{{ entry.hours }}h
							<span v-if="entry.is_running" class="ml-1 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">Běží</span>
						</td>
						<td class="py-3 px-2 text-right text-slate-400">{{ entry.hourly_rate || '—' }}</td>
						<td class="py-3 px-2 text-right">
							<button type="button" class="text-red-400 hover:text-red-600" @click="deleteEntry(entry.id)"><TrashIcon class="size-4" /></button>
						</td>
					</tr>
				</tbody>
			</table>
		</LayoutContainer>
	</div>
</template>
