<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import { DocumentTextIcon, UserIcon, FolderIcon, ArrowDownTrayIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const error = ref(false);
const loading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tabs = ref([
	{ name: 'Základní údaje', link: '#info', current: false },
	{ name: 'Obsah smlouvy', link: '#obsah', current: false },
	{ name: 'Soubory', link: '#soubory', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová smlouva' : 'Detail smlouvy');
const breadcrumbs = ref([
	{ name: 'Smlouvy', link: '/smlouvy', current: false },
	{ name: pageTitle.value, link: '/smlouvy/pridat', current: true },
]);

const employees = ref([]);
const projects = ref([]);

const item = ref({
	id: null,
	title: '',
	description: '',
	type: 'other',
	status: 'draft',
	employee_id: null,
	project_id: null,
	date_from: '',
	date_to: '',
	salary: 0,
	salary_type: 'monthly',
	currency_id: null,
	content: '',
	signed_by_employee: '',
	signed_at: '',
	terms: '',
	benefits: '',
	vacation_days: 20,
	notice_period_days: 60,
	note: '',
	files: [] as any[],
	sites: [] as number[],
});

const typeOptions = ref([
	{ value: 'hpp', name: 'HPP' },
	{ value: 'dpp', name: 'DPP' },
	{ value: 'dpc', name: 'DPČ' },
	{ value: 'osvc', name: 'OSVČ' },
	{ value: 'internship', name: 'Stáž' },
	{ value: 'nda', name: 'NDA' },
	{ value: 'other', name: 'Jiný' },
]);
const statusOptions = ref([
	{ value: 'draft', name: 'Koncept' },
	{ value: 'active', name: 'Aktivní' },
	{ value: 'terminated', name: 'Ukončená' },
	{ value: 'expired', name: 'Vypršelá' },
]);
const salaryTypeOptions = ref([
	{ value: 'monthly', name: 'Měsíčně' },
	{ value: 'hourly', name: 'Hodinově' },
]);

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/contract/' + route.params.id, {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	})
		.then((r) => {
			item.value = {
				...r,
				sites: Array.isArray(r.sites) ? r.sites.map((s: any) => typeof s === 'object' ? s.id : s) : [],
			};
			pageTitle.value = r.title;
			breadcrumbs.value[1] = {
				name: r.title,
				link: '/smlouvy/' + route.params.id,
				current: true,
			};
		})
		.catch(() => {
			error.value = true;
		})
		.finally(() => {
			loading.value = false;
		});
}

async function loadEmployees() {
	const client = useSanctumClient();
	await client('/api/admin/employee', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	})
		.then((r) => {
			const d = r?.data || r;
			employees.value = d.map((e: any) => ({
				value: e.id,
				name: e.full_name || e.first_name + ' ' + e.last_name,
			}));
		})
		.catch(() => {});
}

async function loadProjects() {
	const client = useSanctumClient();
	await client('/api/admin/project', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	})
		.then((r) => {
			const d = r?.data || r;
			projects.value = d.map((p: any) => ({
				value: p.id,
				name: p.name,
			}));
		})
		.catch(() => {});
}

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;
	await client(
		route.params.id === 'pridat'
			? '/api/admin/contract'
			: '/api/admin/contract/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(item.value),
			headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
		},
	)
		.then((r) => {
			$toast.show({
				summary: 'Hotovo',
				detail: route.params.id === 'pridat' ? 'Smlouva vytvořena.' : 'Smlouva upravena.',
				severity: 'success',
			});
			if (!redirect && route.params.id === 'pridat') router.push('/smlouvy/' + r.id);
			else if (redirect) router.push('/smlouvy');
			else loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit smlouvu.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
		});
}

async function uploadFile(event: Event) {
	const target = event.target as HTMLInputElement;
	const file = target.files?.[0];
	if (!file || !item.value.id) return;

	const client = useSanctumClient();
	const formData = new FormData();
	formData.append('file', file);
	formData.append('title', item.value.title || 'Smlouva');
	formData.append('sites', JSON.stringify(item.value.sites));

	loading.value = true;
	await client('/api/admin/contract/' + item.value.id, {
		method: 'POST',
		body: formData,
		headers: { 'X-Site-Hash': selectedSiteHash.value },
	})
		.then(() => {
			$toast.show({ summary: 'Hotovo', detail: 'Soubor nahrán.', severity: 'success' });
			loadItem();
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se nahrát soubor.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
			target.value = '';
		});
}

async function downloadFile(fileId: number) {
	const client = useSanctumClient();
	try {
		const res = await client.raw('/api/admin/contract/' + item.value.id + '/file/' + fileId, {
			method: 'GET',
			credentials: 'include',
			responseType: 'blob',
		});
		if (!res.ok) throw new Error('Chyba');
		const blob = res._data as Blob;
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = 'smlouva-' + item.value.id + '.pdf';
		document.body.appendChild(a);
		a.click();
		a.remove();
		URL.revokeObjectURL(url);
	} catch (e) {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
	}
}

watchEffect(() => {
	if (!route?.path) return;
	const hash = route.hash || '#info';
	tabs.value.forEach((tab) => {
		tab.current = tab.link === hash;
	});
	if (!tabs.value.some((t) => t.current)) {
		tabs.value[0].current = true;
	}
});

useHead({ title: pageTitle.value });
onMounted(() => {
	loadEmployees();
	loadProjects();
	if (route.params.id !== 'pridat') loadItem();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			slug="employee_contracts"
			@save="saveItem"
		/>
		<LayoutTabs :tabs="tabs" class="sticky top-16 z-30" />
		<Form @submit="saveItem">
			<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
				<div class="col-span-1 lg:col-span-9">
					<!-- Info tab -->
					<template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
						<div class="space-y-8">
							<LayoutContainer>
								<div class="mb-6 flex items-center gap-3">
									<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
										<DocumentTextIcon class="size-5" />
									</div>
									<LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
								</div>
								<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
									<BaseFormInput v-model="item.title" label="Název smlouvy" name="title" rules="required" class="col-span-full" />
									<BaseFormSelect v-model="item.type" label="Typ" name="type" :options="typeOptions" />
									<BaseFormSelect v-model="item.status" label="Stav" name="status" :options="statusOptions" />
									<BaseFormInput v-model="item.date_from" label="Platnost od" name="date_from" type="date" />
									<BaseFormInput v-model="item.date_to" label="Platnost do" name="date_to" type="date" />
									<BaseFormTextarea v-model="item.description" label="Popis" name="description" rows="3" class="col-span-full" />
								</div>
							</LayoutContainer>

							<LayoutContainer>
								<div class="mb-6 flex items-center gap-3">
									<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
										<UserIcon class="size-5" />
									</div>
									<LayoutTitle class="!mb-0">Přiřazení</LayoutTitle>
								</div>
								<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
									<BaseFormSelect v-model="item.employee_id" label="Zaměstnanec" name="employee_id" :options="employees" placeholder="-- Žádný --" />
									<BaseFormSelect v-model="item.project_id" label="Projekt" name="project_id" :options="projects" placeholder="-- Žádný --" />
								</div>
							</LayoutContainer>

							<LayoutContainer v-if="item.type !== 'nda' && item.type !== 'other'">
								<div class="mb-6 flex items-center gap-3">
									<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
										<DocumentTextIcon class="size-5" />
									</div>
									<LayoutTitle class="!mb-0">Pracovní podmínky</LayoutTitle>
								</div>
								<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
									<BaseFormInput v-model="item.salary" label="Odměna" name="salary" type="number" />
									<BaseFormSelect v-model="item.salary_type" label="Typ odměny" name="salary_type" :options="salaryTypeOptions" />
									<BaseFormInput v-model="item.vacation_days" label="Dovolená (dní)" name="vacation_days" type="number" />
									<BaseFormInput v-model="item.notice_period_days" label="Výpovědní lhůta (dní)" name="notice_period_days" type="number" />
									<BaseFormInput v-model="item.signed_by_employee" label="Podepsáno zaměstnancem" name="signed_by_employee" />
									<BaseFormInput v-model="item.signed_at" label="Datum podpisu" name="signed_at" type="date" />
									<BaseFormTextarea v-model="item.terms" label="Podmínky" name="terms" rows="3" class="col-span-full" />
									<BaseFormTextarea v-model="item.benefits" label="Benefity" name="benefits" rows="3" class="col-span-full" />
								</div>
							</LayoutContainer>

							<LayoutContainer>
								<BaseFormTextarea v-model="item.note" label="Poznámka" name="note" rows="3" />
							</LayoutContainer>
						</div>
					</template>

					<!-- Content tab (WYSIWYG) -->
					<template v-if="tabs.find((tab) => tab.current && tab.link === '#obsah')">
						<LayoutContainer>
							<div class="mb-6 flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
									<DocumentTextIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Obsah smlouvy</LayoutTitle>
							</div>
							<p class="mb-4 text-sm text-slate-500">
								Obsah smlouvy se při uložení automaticky vygeneruje do PDF.
							</p>
							<BaseFormEditor v-model="item.content" label="Text smlouvy" />
						</LayoutContainer>
					</template>

					<!-- Files tab -->
					<template v-if="tabs.find((tab) => tab.current && tab.link === '#soubory')">
						<LayoutContainer>
							<div class="mb-6 flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
									<FolderIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Soubory</LayoutTitle>
							</div>

							<div v-if="item.id" class="mb-6">
								<label
									class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600"
								>
									<ArrowDownTrayIcon class="size-5 rotate-180" />
									Nahrát soubor
									<input type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" @change="uploadFile" />
								</label>
							</div>
							<div v-else class="mb-6 text-sm text-slate-400">
								Pro nahrání souborů nejprve uložte smlouvu.
							</div>

							<div v-if="!item.files?.length" class="py-12 text-center text-sm text-slate-400">
								Žádné soubory.
							</div>

							<div v-else class="space-y-3">
								<div
									v-for="file in item.files"
									:key="file.id"
									class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
								>
									<div class="flex items-center gap-4">
										<div class="flex size-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
											<DocumentTextIcon class="size-5" />
										</div>
										<div>
											<p class="text-sm font-medium text-slate-900">{{ file.name }}</p>
											<p class="text-xs text-slate-400">
												{{ file.mime_type }}
												<span v-if="file.size" class="ml-2">{{ (file.size / 1024).toFixed(0) }} KB</span>
											</p>
										</div>
									</div>
									<button
										type="button"
										class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
										@click="downloadFile(file.id)"
									>
										Stáhnout
									</button>
								</div>
							</div>
						</LayoutContainer>
					</template>
				</div>

				<div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
					<LayoutActionsDetailBlock
						v-model:sites="item.sites"
						:allow-image="false"
						:allow-is-active="false"
						:allow-translations="false"
					/>
				</div>
			</div>
		</Form>
	</div>
</template>
