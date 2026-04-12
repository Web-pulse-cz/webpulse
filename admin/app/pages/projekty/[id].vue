<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
	{ name: 'Přehled', link: '#prehled', current: false },
	{ name: 'Milníky a úkoly', link: '#ukoly', current: false },
	{ name: 'Sledování času', link: '#cas', current: false },
	{ name: 'Náklady', link: '#naklady', current: false },
	{ name: 'Faktury', link: '#faktury', current: false },
	{ name: 'Poznámky', link: '#poznamky', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový projekt' : 'Detail projektu');

const breadcrumbs = ref([
	{ name: 'Projekty', link: '/projekty', current: false },
	{ name: pageTitle.value, link: '/projekty/pridat', current: true },
]);

const statuses = ref([]);
const tags = ref([]);
const clients = ref([]);
const users = ref([]);

const item = ref({
	id: null,
	name: '',
	description: '',
	note: '',
	image: '',
	client_id: null,
	status_id: null,
	currency_id: null,
	tax_rate_id: null,
	start_date: '',
	deadline_date: '',
	end_date: '',
	hourly_rate: 0,
	expected_hours: 0,
	total_tracked_hours: 0,
	expected_revenue: 0,
	total_revenue: 0,
	total_costs: 0,
	profit: 0,
	is_archived: false,
	tags: [],
	milestones: [],
	tasks: [],
	time_entries: [],
	costs: [],
	notes: [],
});

// Timer state
const timerInterval = ref(null as any);
const timerDisplay = ref('00:00:00');
const runningEntry = ref(null as any);

// ─── Data Loading ──────────────────────────────────────────

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;

	await client('/api/admin/project/' + route.params.id, {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response) => {
			item.value = response;
			pageTitle.value = item.value.name;
			breadcrumbs.value[1] = {
				name: pageTitle.value,
				link: '/projekty/' + route.params.id,
				current: true,
			};
			checkRunningTimer();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst projekt.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
		});
}

async function loadStatuses() {
	const client = useSanctumClient();
	await client('/api/admin/project/status', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response) => {
			statuses.value = response.map((s: any) => ({ value: s.id, name: s.name }));
		})
		.catch(() => {});
}

async function loadTags() {
	const client = useSanctumClient();
	await client('/api/admin/project/tag', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response) => {
			tags.value = response;
		})
		.catch(() => {});
}

async function loadClients() {
	const client = useSanctumClient();
	await client('/api/admin/client', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response) => {
			const data = response?.data || response;
			clients.value = data.map((c: any) => ({ value: c.id, name: c.name }));
		})
		.catch(() => {});
}

async function loadUsers() {
	const client = useSanctumClient();
	await client('/api/admin/user', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response) => {
			const data = response?.data || response;
			users.value = data.map((u: any) => ({ value: u.id, name: u.name || u.email }));
		})
		.catch(() => {});
}

// ─── Save Project ──────────────────────────────────────────

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;

	const payload = {
		...item.value,
		tags: item.value.tags?.map((t: any) => t.id || t) || [],
	};

	await client(
		route.params.id === 'pridat' ? '/api/admin/project' : '/api/admin/project/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(payload),
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		},
	)
		.then((response) => {
			$toast.show({
				summary: 'Hotovo',
				detail: route.params.id === 'pridat' ? 'Projekt byl úspěšně vytvořen.' : 'Projekt byl úspěšně upraven.',
				severity: 'success',
			});
			if (!redirect && route.params.id === 'pridat') {
				router.push('/projekty/' + response.id);
			} else if (redirect) {
				router.push('/projekty');
			} else {
				loadItem();
			}
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit projekt.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
		});
}

// ─── Milestones ────────────────────────────────────────────

const newMilestone = ref({ name: '', due_date: '' });

async function saveMilestone() {
	if (!newMilestone.value.name) return;
	const client = useSanctumClient();

	await client('/api/admin/project/' + route.params.id + '/milestone', {
		method: 'POST',
		body: JSON.stringify(newMilestone.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			newMilestone.value = { name: '', due_date: '' };
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit milník.', severity: 'error' });
		});
}

async function completeMilestone(milestoneId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/milestone/' + milestoneId + '/complete', {
		method: 'POST',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

async function deleteMilestone(milestoneId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/milestone/' + milestoneId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

// ─── Tasks ─────────────────────────────────────────────────

const newTask = ref({ name: '', status: 'pending', priority: 'normal', user_id: null, milestone_id: null });

async function saveTask() {
	if (!newTask.value.name) return;
	const client = useSanctumClient();

	await client('/api/admin/project/' + route.params.id + '/task', {
		method: 'POST',
		body: JSON.stringify(newTask.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			newTask.value = { name: '', status: 'pending', priority: 'normal', user_id: null, milestone_id: null };
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit úkol.', severity: 'error' });
		});
}

async function toggleTaskStatus(task: any) {
	const client = useSanctumClient();
	const newStatus = task.status === 'completed' ? 'pending' : 'completed';

	await client('/api/admin/project/' + route.params.id + '/task/' + task.id, {
		method: 'POST',
		body: JSON.stringify({ ...task, status: newStatus }),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

async function deleteTask(taskId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/task/' + taskId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

// ─── Time Entries & Timer ──────────────────────────────────

const newTimeEntry = ref({ description: '', hours: 0, date: '' });

async function saveTimeEntry() {
	const client = useSanctumClient();

	await client('/api/admin/project/' + route.params.id + '/time-entry', {
		method: 'POST',
		body: JSON.stringify(newTimeEntry.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			newTimeEntry.value = { description: '', hours: 0, date: '' };
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit záznam.', severity: 'error' });
		});
}

async function deleteTimeEntry(entryId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/time-entry/' + entryId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

async function startTimer() {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/timer/start', {
		method: 'POST',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => loadItem())
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se spustit timer.', severity: 'error' });
		});
}

async function stopTimer(entryId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/timer/' + entryId + '/stop', {
		method: 'POST',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			clearInterval(timerInterval.value);
			runningEntry.value = null;
			timerDisplay.value = '00:00:00';
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se zastavit timer.', severity: 'error' });
		});
}

function checkRunningTimer() {
	const running = item.value.time_entries?.find((e: any) => e.is_running);
	if (running) {
		runningEntry.value = running;
		startTimerDisplay(running.timer_started_at);
	}
}

function startTimerDisplay(startedAt: string) {
	clearInterval(timerInterval.value);
	timerInterval.value = setInterval(() => {
		const elapsed = Math.floor((Date.now() - new Date(startedAt).getTime()) / 1000);
		const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
		const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
		const s = String(elapsed % 60).padStart(2, '0');
		timerDisplay.value = `${h}:${m}:${s}`;
	}, 1000);
}

// ─── Costs ─────────────────────────────────────────────────

const newCost = ref({ name: '', amount: 0, category: 'other', date: '' });

const costCategoryOptions = ref([
	{ value: 'software', name: 'Software' },
	{ value: 'hardware', name: 'Hardware' },
	{ value: 'subcontractor', name: 'Subdodavatel' },
	{ value: 'marketing', name: 'Marketing' },
	{ value: 'travel', name: 'Cestovné' },
	{ value: 'other', name: 'Ostatní' },
]);

async function saveCost() {
	if (!newCost.value.name) return;
	const client = useSanctumClient();

	await client('/api/admin/project/' + route.params.id + '/cost', {
		method: 'POST',
		body: JSON.stringify(newCost.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			newCost.value = { name: '', amount: 0, category: 'other', date: '' };
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit náklad.', severity: 'error' });
		});
}

async function deleteCost(costId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/cost/' + costId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

// ─── Notes ─────────────────────────────────────────────────

const newNote = ref({ content: '' });

async function saveNote() {
	if (!newNote.value.content) return;
	const client = useSanctumClient();

	await client('/api/admin/project/' + route.params.id + '/note', {
		method: 'POST',
		body: JSON.stringify(newNote.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then(() => {
			newNote.value = { content: '' };
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit poznámku.', severity: 'error' });
		});
}

async function deleteNote(noteId: number) {
	const client = useSanctumClient();
	await client('/api/admin/project/' + route.params.id + '/note/' + noteId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => loadItem());
}

// ─── Lifecycle ─────────────────────────────────────────────

const taskStatusOptions = ref([
	{ value: 'pending', name: 'Čekající' },
	{ value: 'in_progress', name: 'V řešení' },
	{ value: 'completed', name: 'Hotovo' },
	{ value: 'cancelled', name: 'Zrušeno' },
]);

const priorityOptions = ref([
	{ value: 'low', name: 'Nízká' },
	{ value: 'normal', name: 'Normální' },
	{ value: 'high', name: 'Vysoká' },
	{ value: 'critical', name: 'Kritická' },
]);

watchEffect(() => {
	const routeTabHash = route.hash;
	if (routeTabHash && routeTabHash !== '') {
		tabs.value.forEach((tab) => {
			tab.current = tab.link === routeTabHash;
		});
	} else {
		tabs.value[0].current = true;
		router.push(route.path + '#prehled');
	}
});

onBeforeUnmount(() => {
	clearInterval(timerInterval.value);
});

useHead({ title: pageTitle.value });

onMounted(() => {
	loadStatuses();
	loadTags();
	loadClients();
	loadUsers();
	if (route.params.id !== 'pridat') {
		loadItem();
	}
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			:modify-bottom="false"
			slug="projects"
			@save="saveItem"
		/>

		<LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

		<Form @submit="saveItem">
			<!-- TAB: Přehled -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#prehled')">
				<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
					<div class="col-span-1 space-y-8 lg:col-span-9">
						<LayoutContainer>
							<div class="mb-6 flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
									<DocumentIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
							</div>

							<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
								<BaseFormInput v-model="item.name" label="Název projektu" type="text" name="name" rules="required|min:3" class="col-span-full" />
								<BaseFormTextarea v-model="item.description" label="Popis" name="description" class="col-span-full" rows="3" />
								<BaseFormTextarea v-model="item.note" label="Interní poznámka" name="note" class="col-span-full" rows="2" />

								<BaseFormSelect v-model="item.client_id" label="Klient" name="client_id" :options="clients" />
								<BaseFormInput v-model="item.start_date" label="Datum zahájení" type="date" name="start_date" />
								<BaseFormInput v-model="item.deadline_date" label="Deadline" type="date" name="deadline_date" />
								<BaseFormInput v-model="item.end_date" label="Datum ukončení" type="date" name="end_date" />
							</div>
						</LayoutContainer>

						<LayoutContainer>
							<div class="mb-6 flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
									<BanknotesIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Finance</LayoutTitle>
							</div>

							<div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
								<BaseFormSelect v-model="item.currency_id" label="Měna" name="currency_id" :options="currencyStore.currenciesOptions" />
								<BaseFormSelect v-model="item.tax_rate_id" label="Sazba DPH" name="tax_rate_id" :options="taxRateStore.taxRateOptions" />
								<BaseFormInput v-model="item.hourly_rate" label="Hodinová sazba" type="number" name="hourly_rate" :step="0.01" />
								<BaseFormInput v-model="item.expected_hours" label="Očekávané hodiny" type="number" name="expected_hours" :step="0.01" />
							</div>

							<div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
								<div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
									<div class="text-2xl font-bold text-slate-900">{{ item.total_tracked_hours }}</div>
									<div class="text-xs text-slate-500">Odpracováno h</div>
								</div>
								<div class="rounded-xl bg-emerald-50 p-4 text-center ring-1 ring-emerald-200">
									<div class="text-2xl font-bold text-emerald-700">{{ item.total_revenue }}</div>
									<div class="text-xs text-emerald-600">Příjmy</div>
								</div>
								<div class="rounded-xl bg-red-50 p-4 text-center ring-1 ring-red-200">
									<div class="text-2xl font-bold text-red-700">{{ item.total_costs }}</div>
									<div class="text-xs text-red-600">Náklady</div>
								</div>
								<div class="rounded-xl bg-indigo-50 p-4 text-center ring-1 ring-indigo-200">
									<div class="text-2xl font-bold text-indigo-700">{{ item.profit }}</div>
									<div class="text-xs text-indigo-600">Zisk</div>
								</div>
							</div>
						</LayoutContainer>
					</div>

					<div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
						<LayoutContainer class="!py-6">
							<LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">Stav projektu</LayoutTitle>
							<div class="mt-4">
								<BaseFormSelect v-model="item.status_id" label="" name="status_id" :options="statuses" />
							</div>
							<div class="mt-4">
								<BaseFormCheckbox v-model="item.is_archived" label="Archivovaný" name="is_archived" />
							</div>
						</LayoutContainer>

						<LayoutContainer class="!py-6">
							<LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">Tagy</LayoutTitle>
							<div class="mt-3 flex flex-wrap gap-2">
								<label
									v-for="tag in tags"
									:key="tag.id"
									class="cursor-pointer rounded-full px-3 py-1 text-xs font-medium transition"
									:class="item.tags?.some((t) => (t.id || t) === tag.id) ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
								>
									<input
										type="checkbox"
										class="hidden"
										:checked="item.tags?.some((t) => (t.id || t) === tag.id)"
										@change="
											item.tags?.some((t) => (t.id || t) === tag.id)
												? (item.tags = item.tags.filter((t) => (t.id || t) !== tag.id))
												: item.tags.push(tag)
										"
									/>
									{{ tag.name }}
								</label>
							</div>
						</LayoutContainer>
					</div>
				</div>
			</template>

			<!-- TAB: Milníky a úkoly -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#ukoly')">
				<div class="space-y-8">
					<LayoutContainer>
						<div class="mb-4 flex items-center justify-between">
							<LayoutTitle class="!mb-0">Milníky</LayoutTitle>
						</div>
						<div class="mb-4 flex gap-3">
							<BaseFormInput v-model="newMilestone.name" label="" name="new_milestone" placeholder="Název milníku" class="flex-1" />
							<BaseFormInput v-model="newMilestone.due_date" label="" type="date" name="milestone_due" class="w-40" />
							<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveMilestone">Přidat</button>
						</div>
						<div class="divide-y divide-slate-100">
							<div v-for="milestone in item.milestones" :key="milestone.id" class="flex items-center justify-between py-3">
								<div class="flex items-center gap-3">
									<button type="button" class="size-5 rounded border transition" :class="milestone.completed_at ? 'border-green-500 bg-green-500 text-white' : 'border-slate-300'" @click="completeMilestone(milestone.id)">
										<span v-if="milestone.completed_at" class="text-xs">&#10003;</span>
									</button>
									<span :class="{ 'line-through text-slate-400': milestone.completed_at }">{{ milestone.name }}</span>
									<span v-if="milestone.due_date" class="text-xs text-slate-400">{{ milestone.due_date }}</span>
								</div>
								<button type="button" class="text-red-400 hover:text-red-600 transition" @click="deleteMilestone(milestone.id)">
									<TrashIcon class="size-4" />
								</button>
							</div>
						</div>
					</LayoutContainer>

					<LayoutContainer>
						<div class="mb-4 flex items-center justify-between">
							<LayoutTitle class="!mb-0">Úkoly</LayoutTitle>
						</div>
						<div class="mb-4 flex gap-3">
							<BaseFormInput v-model="newTask.name" label="" name="new_task" placeholder="Název úkolu" class="flex-1" />
							<BaseFormSelect v-model="newTask.priority" label="" name="new_task_priority" :options="priorityOptions" class="w-32" />
							<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveTask">Přidat</button>
						</div>
						<div class="divide-y divide-slate-100">
							<div v-for="task in item.tasks" :key="task.id" class="flex items-center justify-between py-3">
								<div class="flex items-center gap-3">
									<button type="button" class="size-5 rounded border transition" :class="task.status === 'completed' ? 'border-green-500 bg-green-500 text-white' : 'border-slate-300'" @click="toggleTaskStatus(task)">
										<span v-if="task.status === 'completed'" class="text-xs">&#10003;</span>
									</button>
									<span :class="{ 'line-through text-slate-400': task.status === 'completed' }">{{ task.name }}</span>
									<span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="{
										'bg-red-100 text-red-700': task.priority === 'critical',
										'bg-orange-100 text-orange-700': task.priority === 'high',
										'bg-slate-100 text-slate-600': task.priority === 'normal',
										'bg-blue-100 text-blue-600': task.priority === 'low',
									}">{{ task.priority }}</span>
									<span v-if="task.user_name" class="text-xs text-slate-400">{{ task.user_name }}</span>
								</div>
								<button type="button" class="text-red-400 hover:text-red-600 transition" @click="deleteTask(task.id)">
									<TrashIcon class="size-4" />
								</button>
							</div>
						</div>
					</LayoutContainer>
				</div>
			</template>

			<!-- TAB: Sledování času -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#cas')">
				<div class="space-y-8">
					<!-- Timer -->
					<LayoutContainer>
						<div class="flex items-center justify-between">
							<div>
								<LayoutTitle class="!mb-0">Timer</LayoutTitle>
								<p v-if="runningEntry" class="mt-1 text-sm text-slate-500">Běží od {{ new Date(runningEntry.timer_started_at).toLocaleTimeString() }}</p>
							</div>
							<div class="flex items-center gap-4">
								<span class="font-mono text-3xl font-bold" :class="runningEntry ? 'text-indigo-600' : 'text-slate-300'">{{ timerDisplay }}</span>
								<button
									v-if="!runningEntry"
									type="button"
									class="rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-500 transition"
									@click="startTimer"
								>
									Start
								</button>
								<button
									v-else
									type="button"
									class="rounded-lg bg-red-600 px-6 py-3 text-sm font-medium text-white hover:bg-red-500 transition"
									@click="stopTimer(runningEntry.id)"
								>
									Stop
								</button>
							</div>
						</div>
					</LayoutContainer>

					<!-- Manual entry -->
					<LayoutContainer>
						<LayoutTitle>Ruční záznam</LayoutTitle>
						<div class="flex gap-3">
							<BaseFormInput v-model="newTimeEntry.description" label="" name="te_desc" placeholder="Popis práce" class="flex-1" />
							<BaseFormInput v-model="newTimeEntry.hours" label="" type="number" name="te_hours" placeholder="Hodiny" :step="0.25" class="w-24" />
							<BaseFormInput v-model="newTimeEntry.date" label="" type="date" name="te_date" class="w-40" />
							<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveTimeEntry">Přidat</button>
						</div>
					</LayoutContainer>

					<!-- Time entries list -->
					<LayoutContainer>
						<LayoutTitle>Záznamy</LayoutTitle>
						<div v-if="!item.time_entries?.length" class="py-8 text-center text-sm text-slate-400">Žádné záznamy.</div>
						<div v-else class="divide-y divide-slate-100">
							<div v-for="entry in item.time_entries" :key="entry.id" class="flex items-center justify-between py-3">
								<div>
									<span class="font-medium">{{ entry.hours }}h</span>
									<span class="ml-2 text-sm text-slate-500">{{ entry.description || '—' }}</span>
									<span class="ml-2 text-xs text-slate-400">{{ entry.date }}</span>
									<span v-if="entry.user_name" class="ml-2 text-xs text-slate-400">{{ entry.user_name }}</span>
									<span v-if="entry.is_running" class="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Běží</span>
								</div>
								<button type="button" class="text-red-400 hover:text-red-600 transition" @click="deleteTimeEntry(entry.id)">
									<TrashIcon class="size-4" />
								</button>
							</div>
						</div>
					</LayoutContainer>
				</div>
			</template>

			<!-- TAB: Náklady -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#naklady')">
				<LayoutContainer>
					<LayoutTitle>Náklady projektu</LayoutTitle>
					<div class="mb-4 flex gap-3">
						<BaseFormInput v-model="newCost.name" label="" name="cost_name" placeholder="Název" class="flex-1" />
						<BaseFormInput v-model="newCost.amount" label="" type="number" name="cost_amount" placeholder="Částka" :step="0.01" class="w-32" />
						<BaseFormSelect v-model="newCost.category" label="" name="cost_cat" :options="costCategoryOptions" class="w-40" />
						<BaseFormInput v-model="newCost.date" label="" type="date" name="cost_date" class="w-40" />
						<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveCost">Přidat</button>
					</div>
					<div v-if="!item.costs?.length" class="py-8 text-center text-sm text-slate-400">Žádné náklady.</div>
					<div v-else class="divide-y divide-slate-100">
						<div v-for="cost in item.costs" :key="cost.id" class="flex items-center justify-between py-3">
							<div>
								<span class="font-medium">{{ cost.name }}</span>
								<span class="ml-2 text-sm font-bold text-red-600">{{ cost.amount }}</span>
								<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ cost.category }}</span>
								<span v-if="cost.date" class="ml-2 text-xs text-slate-400">{{ cost.date }}</span>
							</div>
							<button type="button" class="text-red-400 hover:text-red-600 transition" @click="deleteCost(cost.id)">
								<TrashIcon class="size-4" />
							</button>
						</div>
					</div>
				</LayoutContainer>
			</template>

			<!-- TAB: Faktury -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#faktury')">
				<LayoutContainer>
					<LayoutTitle>Faktury projektu</LayoutTitle>
					<div class="py-8 text-center text-sm text-slate-400">
						Faktury přiřazené k tomuto projektu se zobrazí zde. Fakturu přiřadíte v detailu faktury.
					</div>
				</LayoutContainer>
			</template>

			<!-- TAB: Poznámky -->
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#poznamky')">
				<LayoutContainer>
					<LayoutTitle>Poznámky</LayoutTitle>
					<div class="mb-4 flex gap-3">
						<BaseFormTextarea v-model="newNote.content" label="" name="note_content" placeholder="Napište poznámku..." rows="2" class="flex-1" />
						<button type="button" class="self-end rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveNote">Přidat</button>
					</div>
					<div v-if="!item.notes?.length" class="py-8 text-center text-sm text-slate-400">Žádné poznámky.</div>
					<div v-else class="space-y-3">
						<div v-for="note in item.notes" :key="note.id" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
							<div class="flex items-start justify-between">
								<div>
									<p class="whitespace-pre-wrap text-sm text-slate-700">{{ note.content }}</p>
									<p class="mt-2 text-xs text-slate-400">
										{{ note.user_name }} &middot; {{ note.created_at ? new Date(note.created_at).toLocaleString() : '' }}
									</p>
								</div>
								<button type="button" class="text-red-400 hover:text-red-600 transition" @click="deleteNote(note.id)">
									<TrashIcon class="size-4" />
								</button>
							</div>
						</div>
					</div>
				</LayoutContainer>
			</template>
		</Form>
	</div>
</template>
