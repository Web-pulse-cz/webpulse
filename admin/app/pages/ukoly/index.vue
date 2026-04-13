<script setup lang="ts">
import { ref, inject, computed } from 'vue';
import { definePageMeta } from '#imports';
import {
	PlusIcon, FunnelIcon, ChevronDownIcon, ChevronRightIcon, TrashIcon,
	XMarkIcon, ChatBubbleLeftIcon, ClockIcon, UserIcon, FolderIcon,
	CheckCircleIcon, PencilSquareIcon, PlayIcon, StopIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const { formatSeconds } = useFormat();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const pageTitle = ref('Úkoly');
const loading = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/ukoly', current: true }]);

// Data
const boards = ref([]);
const projects = ref([]);
const users = ref([]);
const expandedBoards = ref<Record<number, boolean>>({});

// Filters
const filterProject = ref(null as number | null);
const filterAssignee = ref(null as number | null);

// Drawer
const showDrawer = ref(false);
const selectedTask = ref(null as any);
const newComment = ref('');

// New task
const newTask = ref({ name: '', global_board_id: null, project_id: null, priority: 'normal' });

// (Boardy se spravují na /ukoly/boardy)

const priorityOptions = ref([
	{ value: 'low', name: 'Nízká' }, { value: 'normal', name: 'Normální' },
	{ value: 'high', name: 'Vysoká' }, { value: 'critical', name: 'Kritická' },
]);

const priorityColors: Record<string, string> = {
	critical: 'bg-red-100 text-red-700', high: 'bg-orange-100 text-orange-700',
	normal: 'bg-slate-100 text-slate-600', low: 'bg-blue-100 text-blue-600',
};

// ─── Loaders ───────────────────────────────────────────────

async function loadBoards() {
	loading.value = true;
	const client = useSanctumClient();
	const query: Record<string, any> = { with_tasks: true };
	if (filterProject.value) query.project_id = filterProject.value;
	if (filterAssignee.value) query.assignee_id = filterAssignee.value;

	await client('/api/admin/task-board', {
		method: 'GET', query,
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		boards.value = r;
		// Auto-expand all boards on first load
		if (Object.keys(expandedBoards.value).length === 0) {
			r.forEach((b: any) => { expandedBoards.value[b.id] = true; });
		}
	}).finally(() => { loading.value = false; });
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

async function loadUsers() {
	const client = useSanctumClient();
	await client('/api/admin/user', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		const d = r?.data || r;
		users.value = d.map((u: any) => ({ value: u.id, name: u.name || u.email }));
	}).catch(() => {});
}

// ─── Boards CRUD ───────────────────────────────────────────

async function deleteBoard(boardId: number) {
	const client = useSanctumClient();
	await client('/api/admin/task-board/' + boardId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => { loadBoards(); });
}

function toggleBoard(boardId: number) {
	expandedBoards.value[boardId] = !expandedBoards.value[boardId];
}

// ─── Tasks CRUD ────────────────────────────────────────────

async function createTask() {
	if (!newTask.value.name) return;
	const client = useSanctumClient();
	await client('/api/admin/task', {
		method: 'POST',
		body: JSON.stringify(newTask.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then(() => {
		newTask.value = { name: '', global_board_id: null, project_id: null, priority: 'normal' };
		loadBoards();
	}).catch(() => {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se vytvořit úkol.', severity: 'error' });
	});
}

async function openTaskDrawer(taskId: number) {
	const client = useSanctumClient();
	await client('/api/admin/task/' + taskId, {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		selectedTask.value = r;
		showDrawer.value = true;
	});
}

async function saveTask() {
	if (!selectedTask.value) return;
	const client = useSanctumClient();
	await client('/api/admin/task/' + selectedTask.value.id, {
		method: 'POST',
		body: JSON.stringify({
			...selectedTask.value,
			assignees: selectedTask.value.assignees?.map((a: any) => a.id || a) || [],
		}),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then(() => {
		$toast.show({ summary: 'Hotovo', detail: 'Úkol uložen.', severity: 'success' });
		loadBoards();
	}).catch(() => {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit úkol.', severity: 'error' });
	});
}

async function moveTask(taskId: number, boardId: number) {
	const client = useSanctumClient();
	await client('/api/admin/task/' + taskId + '/move', {
		method: 'POST',
		body: JSON.stringify({ global_board_id: boardId }),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then(() => { loadBoards(); });
}

async function deleteTask(taskId: number) {
	const client = useSanctumClient();
	await client('/api/admin/task/' + taskId, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => {
		showDrawer.value = false;
		selectedTask.value = null;
		loadBoards();
	});
}

// ─── Task Timer ────────────────────────────────────────────

const runningTimerId = ref(null as number | null);

const taskTimerRunning = computed(() => {
	return selectedTask.value && runningTimerId.value === selectedTask.value.id;
});

async function loadRunningTimer() {
	const client = useSanctumClient();
	try {
		const r = await client('/api/admin/time-entry/running', {
			method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		});
		runningTimerId.value = r?.task_id || null;
	} catch {
		runningTimerId.value = null;
	}
}

async function startTaskTimer() {
	if (!selectedTask.value) return;
	const client = useSanctumClient();
	await client('/api/admin/time-entry/timer/start', {
		method: 'POST',
		body: JSON.stringify({
			task_id: selectedTask.value.id,
			project_id: selectedTask.value.project_id,
			description: selectedTask.value.name,
		}),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then(() => {
		runningTimerId.value = selectedTask.value.id;
		$toast.show({ summary: 'Timer spuštěn', detail: 'Přejděte na Sledování času pro zobrazení.', severity: 'success' });
	}).catch(() => {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se spustit timer.', severity: 'error' });
	});
}

async function stopTaskTimer() {
	const client = useSanctumClient();
	try {
		const r = await client('/api/admin/time-entry/running', {
			method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		});
		if (r?.id) {
			await client('/api/admin/time-entry/timer/' + r.id + '/stop', {
				method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
			});
			runningTimerId.value = null;
			$toast.show({ summary: 'Timer zastaven', detail: 'Záznam uložen.', severity: 'success' });
		}
	} catch {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se zastavit timer.', severity: 'error' });
	}
}

// ─── Comments ──────────────────────────────────────────────

async function addComment() {
	if (!newComment.value || !selectedTask.value?.project_id) return;
	const client = useSanctumClient();
	await client('/api/admin/project/' + selectedTask.value.project_id + '/task/' + selectedTask.value.id + '/comment', {
		method: 'POST',
		body: JSON.stringify({ content: newComment.value }),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => {
		newComment.value = '';
		openTaskDrawer(selectedTask.value.id);
	});
}

// ─── Lifecycle ─────────────────────────────────────────────

function applyFilters() {
	loadBoards();
}

watch(selectedSiteHash, () => { loadBoards(); loadProjects(); });

useHead({ title: pageTitle.value });
onMounted(() => { loadBoards(); loadProjects(); loadUsers(); loadRunningTimer(); });
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			slug="project_tasks"
			:inline-filters="[
				{ key: 'project_id', label: 'Projekt', type: 'select', modelValue: filterProject, options: [{ value: '', name: 'Všechny projekty' }, ...projects] },
				{ key: 'assignee_id', label: 'Přiřazeno', type: 'select', modelValue: filterAssignee, options: [{ value: '', name: 'Všichni' }, ...users] },
			]"
			@apply-filters="(p) => { if (p.key === 'project_id') filterProject = p.value || null; else if (p.key === 'assignee_id') filterAssignee = p.value || null; else if (p.key === '_apply') applyFilters(); }"
		/>

		<!-- New task -->
		<div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
			<div class="flex flex-col gap-3 sm:flex-row">
				<BaseFormInput v-model="newTask.name" label="" name="new_task" placeholder="Nový úkol..." class="flex-1" />
				<div class="flex flex-wrap gap-3">
					<BaseFormSelect v-model="newTask.global_board_id" label="" name="nt_board" :options="boards.map((b) => ({ value: b.id, name: b.name }))" class="w-full sm:w-36" />
					<BaseFormSelect v-model="newTask.project_id" label="" name="nt_project" :options="[{ value: null, name: 'Bez projektu' }, ...projects]" class="w-full sm:w-36" />
					<BaseFormSelect v-model="newTask.priority" label="" name="nt_prio" :options="priorityOptions" class="w-full sm:w-28" />
					<button type="button" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition sm:w-auto" @click="createTask">
						<PlusIcon class="mr-1 inline size-4" /> Přidat
					</button>
				</div>
			</div>
		</div>

		<!-- Boards as accordion sections -->
		<div v-if="!boards.length && !loading" class="py-16 text-center text-sm text-slate-400">
			Vytvořte první board pro organizaci úkolů.
		</div>

		<div class="space-y-4">
			<div v-for="board in boards" :key="board.id" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
				<!-- Board header -->
				<div
					class="flex cursor-pointer items-center justify-between px-5 py-4 transition hover:bg-slate-50"
					:style="{ borderLeft: '4px solid ' + board.color }"
					@click="toggleBoard(board.id)"
				>
					<div class="flex items-center gap-3">
						<component :is="expandedBoards[board.id] ? ChevronDownIcon : ChevronRightIcon" class="size-5 text-slate-400" />
						<span class="text-sm font-bold text-slate-900">{{ board.name }}</span>
						<span v-if="board.is_completed" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Dokončený</span>
						<span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ board.tasks?.length || 0 }}</span>
					</div>
					<div class="flex items-center gap-2">
						<button type="button" class="rounded-lg p-1.5 text-slate-300 hover:bg-red-50 hover:text-red-500 transition" @click.stop="deleteBoard(board.id)">
							<TrashIcon class="size-4" />
						</button>
					</div>
				</div>

				<!-- Tasks -->
				<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
					<div v-if="expandedBoards[board.id]" class="divide-y divide-slate-100 border-t border-slate-100">
						<div
							v-for="task in board.tasks"
							:key="task.id"
							class="cursor-pointer px-4 py-3 transition hover:bg-slate-50/80 sm:px-5"
							@click="openTaskDrawer(task.id)"
						>
							<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
								<div class="flex flex-wrap items-center gap-2">
									<span class="font-mono text-[10px] font-bold text-indigo-500">{{ task.code }}</span>
									<span class="text-sm font-medium text-slate-900">{{ task.name }}</span>
									<span v-if="task.priority && task.priority !== 'normal'" class="rounded-full px-1.5 py-0.5 text-[9px] font-bold" :class="priorityColors[task.priority]">{{ task.priority }}</span>
									<span v-if="task.project_name" class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">{{ task.project_name }}</span>
								</div>
								<div class="flex flex-wrap items-center gap-2 sm:gap-3">
									<div class="flex -space-x-1">
										<div v-for="a in (task.assignees || []).slice(0, 3)" :key="a.id" class="flex size-6 items-center justify-center rounded-full bg-slate-200 text-[9px] font-bold text-slate-600 ring-1 ring-white">{{ a.name?.charAt(0) }}</div>
									</div>
									<span v-if="task.due_date" class="text-xs text-slate-400">{{ task.due_date }}</span>
									<!-- Move buttons (hidden on mobile) -->
									<div class="hidden gap-1 sm:flex" @click.stop>
										<button
											v-for="targetBoard in boards.filter((b) => b.id !== board.id)"
											:key="targetBoard.id"
											type="button"
											class="rounded px-1.5 py-0.5 text-[9px] font-medium text-slate-400 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-700 transition"
											@click="moveTask(task.id, targetBoard.id)"
										>
											&rarr; {{ targetBoard.name }}
										</button>
									</div>
								</div>
							</div>
						</div>

						<div v-if="!board.tasks?.length" class="px-5 py-8 text-center text-xs text-slate-400">
							Žádné úkoly v tomto boardu.
						</div>
					</div>
				</Transition>
			</div>
		</div>

		<!-- Task Drawer -->
		<Teleport to="body">
			<Transition
				enter-active-class="transition duration-300 ease-out"
				enter-from-class="translate-x-full"
				enter-to-class="translate-x-0"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="translate-x-0"
				leave-to-class="translate-x-full"
			>
				<div v-if="showDrawer && selectedTask" class="fixed inset-y-0 right-0 z-50 flex">
					<!-- Backdrop -->
					<div class="fixed inset-0 bg-black/30" @click="showDrawer = false"></div>

					<!-- Drawer panel -->
					<div class="relative ml-auto flex h-full w-full flex-col bg-white shadow-2xl" style="max-width: 700px;">
						<!-- Header -->
						<div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
							<div>
								<span class="font-mono text-xs font-bold text-indigo-500">{{ selectedTask.code }}</span>
								<h2 class="text-lg font-bold text-slate-900">{{ selectedTask.name }}</h2>
							</div>
							<button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" @click="showDrawer = false">
								<XMarkIcon class="size-5" />
							</button>
						</div>

						<!-- Content (scrollable) -->
						<div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
							<BaseFormInput v-model="selectedTask.name" label="Název" name="task_name" />
							<BaseFormTextarea v-model="selectedTask.description" label="Popis" name="task_desc" rows="4" />

							<div class="grid grid-cols-2 gap-4">
								<BaseFormSelect v-model="selectedTask.global_board_id" label="Board" name="task_board" :options="boards.map((b) => ({ value: b.id, name: b.name }))" />
								<BaseFormSelect v-model="selectedTask.project_id" label="Projekt" name="task_project" :options="[{ value: null, name: 'Bez projektu' }, ...projects]" />
								<BaseFormSelect v-model="selectedTask.priority" label="Priorita" name="task_prio" :options="priorityOptions" />
								<BaseFormInput v-model="selectedTask.estimated_hours" label="Odhad hodin" type="number" name="task_est" :step="0.5" />
								<BaseFormInput v-model="selectedTask.due_date" label="Termín" type="date" name="task_due" class="col-span-2" />
							</div>

							<!-- Assignees -->
							<div>
								<label class="mb-1 block text-xs font-medium text-slate-700">Přiřazení</label>
								<div class="max-h-32 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2">
									<label v-for="user in users" :key="user.value" class="flex items-center gap-2 rounded p-1 text-xs hover:bg-slate-50">
										<input type="checkbox" :checked="selectedTask.assignees?.some((a) => (a.id || a) === user.value)" class="rounded text-indigo-600" @change="selectedTask.assignees?.some((a) => (a.id || a) === user.value) ? (selectedTask.assignees = selectedTask.assignees.filter((a) => (a.id || a) !== user.value)) : selectedTask.assignees.push({ id: user.value, name: user.name })" />
										{{ user.name }}
									</label>
								</div>
							</div>

							<!-- Time entries -->
							<div v-if="selectedTask.time_entries?.length" class="border-t border-slate-100 pt-4">
								<h3 class="mb-2 flex items-center gap-2 text-xs font-bold text-slate-700">
									<ClockIcon class="size-4" /> Čas ({{ formatSeconds(selectedTask.total_tracked_seconds) }})
								</h3>
								<div class="space-y-1">
									<div v-for="te in selectedTask.time_entries" :key="te.id" class="flex items-center justify-between text-xs text-slate-600">
										<span>{{ formatSeconds(te.seconds) }} — {{ te.description || '—' }} ({{ te.date }})</span>
										<span>{{ te.user_name }}</span>
									</div>
								</div>
							</div>

							<!-- Comments -->
							<div class="border-t border-slate-100 pt-4">
								<h3 class="mb-3 flex items-center gap-2 text-xs font-bold text-slate-700">
									<ChatBubbleLeftIcon class="size-4" /> Komentáře ({{ selectedTask.comments?.length || 0 }})
								</h3>
								<div v-if="selectedTask.project_id" class="mb-3 flex gap-2">
									<BaseFormInput v-model="newComment" label="" name="comment" placeholder="Napište komentář..." class="flex-1" />
									<button type="button" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500" @click="addComment">Odeslat</button>
								</div>
								<div class="max-h-40 space-y-2 overflow-y-auto">
									<div v-for="c in selectedTask.comments" :key="c.id" class="rounded-lg bg-slate-50 p-3">
										<p class="text-sm text-slate-700">{{ c.content }}</p>
										<p class="mt-1 text-[10px] text-slate-400">{{ c.user_name }} &middot; {{ c.created_at ? new Date(c.created_at).toLocaleString('cs-CZ') : '' }}</p>
									</div>
								</div>
							</div>
						</div>

						<!-- Timer + Footer actions -->
						<div class="border-t border-slate-200 px-6 py-3">
							<button
								v-if="!taskTimerRunning"
								type="button"
								class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100"
								@click="startTaskTimer"
							>
								<PlayIcon class="size-4" /> Spustit timer
							</button>
							<button
								v-else
								type="button"
								class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-100"
								@click="stopTaskTimer"
							>
								<StopIcon class="size-4" /> Zastavit timer
							</button>
						</div>
						<div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
							<button type="button" class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-200 transition" @click="deleteTask(selectedTask.id)">
								<TrashIcon class="mr-1 inline size-4" /> Smazat
							</button>
							<button type="button" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition" @click="saveTask">
								<CheckCircleIcon class="mr-1 inline size-4" /> Uložit
							</button>
						</div>
					</div>
				</div>
			</Transition>
		</Teleport>
	</div>
</template>
