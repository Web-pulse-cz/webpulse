<script setup lang="ts">
import { ref } from 'vue';

import {
	ArchiveBoxIcon,
	CalendarDaysIcon,
	DocumentIcon,
	NewspaperIcon,
	RocketLaunchIcon,
} from '@heroicons/vue/24/outline';
import { useCashflowCategoryStore } from '~/../stores/cashflowCategoryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';

const cashflowCategoryStore = useCashflowCategoryStore();
const currencyStore = useCurrencyStore();

const { $toast } = useNuxtApp();
const pageTitle = ref('Přehled');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([]);

const dashboard = ref<any>({
	posts: { count: 0, data: [] },
	novelties: { count: 0, data: [] },
	events: { count: 0, data: [] },
	pages: { count: 0, data: [] },
});
const changelog = ref([]);

const cashflowActionDialog = ref({
	show: false as boolean,
	day: 0 as number,
	categoryId: null as number | null,
});

const contentCards = computed(() => [
	{
		key: 'posts',
		title: 'Články',
		icon: ArchiveBoxIcon,
		link: '/obsah/clanky',
		color: 'indigo',
		data: dashboard.value.posts,
	},
	{
		key: 'novelties',
		title: 'Novinky',
		icon: NewspaperIcon,
		link: '/obsah/novinky',
		color: 'emerald',
		data: dashboard.value.novelties,
	},
	{
		key: 'events',
		title: 'Události',
		icon: CalendarDaysIcon,
		link: '/obsah/udalosti',
		color: 'amber',
		data: dashboard.value.events,
	},
	{
		key: 'pages',
		title: 'Informační stránky',
		icon: DocumentIcon,
		link: '/obsah/stranky',
		color: 'sky',
		data: dashboard.value.pages,
	},
]);

const colorClasses: Record<string, { bg: string; text: string; count: string }> = {
	indigo: { bg: 'bg-indigo-50', text: 'text-indigo-600', count: 'text-indigo-600' },
	emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', count: 'text-emerald-600' },
	amber: { bg: 'bg-amber-50', text: 'text-amber-600', count: 'text-amber-600' },
	sky: { bg: 'bg-sky-50', text: 'text-sky-600', count: 'text-sky-600' },
};

async function loadDashboard() {
	loading.value = true;
	const client = useSanctumClient();

	await client('/api/admin/dashboard', {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then((response) => {
			dashboard.value = response;
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se načíst přehled. Zkuste to prosím později.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

async function loadChangelog() {
	loading.value = true;
	const client = useSanctumClient();

	await client('/api/admin/changelog', {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
		query: {
			orderBy: 'id',
			orderWay: 'desc',
		},
	})
		.then((response) => {
			changelog.value = response;
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se načíst changelog. Zkuste to prosím později.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

function openCashflowDialog() {
	cashflowActionDialog.value.show = true;
	cashflowActionDialog.value.categoryId = 50;
	cashflowActionDialog.value.day = new Date().getDate();
}

async function saveDayRecords(data: {
	categoryId: number | null;
	currencyId: number;
	day: number;
	type: string;
	dayRecords: Array<{ id: number | null; amount: number; description: string }>;
}) {
	const client = useSanctumClient();
	error.value = false;

	const month = new Date().getMonth() + 1;
	const year = new Date().getFullYear();

	const formattedDate = new Date(Date.UTC(year, month - 1, data.day)).toISOString();

	const categoryId = data.categoryId ? data.categoryId : null;
	const currencyId = data.currencyId ? data.currencyId : null;
	const type = data.type ? data.type : 'expense';
	const records = data.dayRecords.map((record) => ({
		id: record.id,
		amount: record.amount,
		description: record.description,
	}));

	await client(categoryId ? '/api/admin/cashflow/' + categoryId : '/api/admin/cashflow', {
		method: 'POST',
		body: JSON.stringify({
			categoryId,
			currencyId,
			formattedDate,
			type,
			records,
		}),
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then(() => {
			$toast.show({
				summary: 'Hotovo',
				detail: 'Záznamy byly úspěšně uložen.',
				severity: 'success',
			});
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail:
					'Nepodařilo se uložit záznamy. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

useHead({
	title: pageTitle.value,
});

onMounted(() => {
	loadDashboard();
	loadChangelog();
});
definePageMeta({
	middleware: 'sanctum:auth',
});
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'add-cashflow', text: 'Zaznamenat výdaj' }]"
			@open-cashflow-dialog="openCashflowDialog"
		/>

		<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
			<div class="col-span-1 space-y-8 lg:col-span-8">
				<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
					<NuxtLink
						v-for="card in contentCards"
						:key="card.key"
						:to="card.link"
						class="group block rounded-2xl bg-white p-6 ring-1 ring-slate-100 transition hover:-translate-y-0.5 hover:ring-slate-200 hover:shadow-sm"
					>
						<div class="mb-5 flex items-center justify-between">
							<div class="flex items-center gap-3">
								<div
									class="flex size-9 items-center justify-center rounded-lg"
									:class="[colorClasses[card.color].bg, colorClasses[card.color].text]"
								>
									<component :is="card.icon" class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">{{ card.title }}</LayoutTitle>
							</div>
							<div
								class="flex items-baseline gap-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"
							>
								Celkem:
								<span class="text-sm/none font-black" :class="colorClasses[card.color].count">{{
									card.data?.count || 0
								}}</span>
							</div>
						</div>

						<div v-if="card.data?.data?.length" class="space-y-2">
							<div
								v-for="item in card.data.data"
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
							Zatím žádné položky
						</div>
					</NuxtLink>
				</div>
			</div>

			<aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-4">
				<LayoutContainer class="!py-6">
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

					<div
						class="custom-scrollbar max-h-[calc(100vh-200px)] space-y-6 overflow-y-auto pb-2 pr-2"
					>
						<ChangelogCard
							v-for="(changelogItem, index) in changelog"
							:key="index"
							:changelog="changelogItem"
						/>
					</div>
				</LayoutContainer>

				<div class="mt-6 rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100/50">
					<p class="text-sm leading-relaxed text-indigo-800/80">
						<strong>Tip:</strong> Zaznamenávejte schůzky a hovory ihned po jejich skončení. Udržíte
						tak histori kontaktu v CRM aktuální a nezapomenete na důležité detaily.
					</p>
				</div>
			</aside>
		</div>

		<CashflowDialogExtendedAction
			v-model:show="cashflowActionDialog.show"
			:categories="cashflowCategoryStore.categoriesOptions"
			:currencies="currencyStore.currenciesOptions"
			:day="cashflowActionDialog.day"
			:type="cashflowActionDialog.type"
			@save-day-records="saveDayRecords($event)"
		/>
	</div>
</template>

<style scoped>
/* Jemný scrollbar pro Changelog sloupec, aby nerušil design */
.custom-scrollbar::-webkit-scrollbar {
	width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
	background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
	background: #cbd5e1; /* slate-300 */
	border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
	background: #94a3b8; /* slate-400 */
}
</style>
