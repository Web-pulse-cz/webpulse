<script setup lang="ts">
import { inject, ref } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Rezervace ubytování');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/ubytovani/rezervace', current: true }]);
const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const activeTab = ref<'upcoming' | 'current' | 'past'>('upcoming');
const tabs = [
	{ key: 'upcoming', label: 'Budoucí rezervace' },
	{ key: 'current', label: 'Aktuálně probíhající' },
	{ key: 'past', label: 'Minulé a zrušené' },
];

const tableQuery = ref({
	search: null as string | null,
	tab: 'upcoming' as string,
	paginate: 20 as number,
	page: 1 as number,
	orderBy: 'start_date' as string,
	orderWay: 'desc' as string,
});

const items = ref([]);

async function loadItems() {
	loading.value = true;
	tableQuery.value.tab = activeTab.value;
	const client = useSanctumClient();

	await client('/api/admin/apartment/reservation', {
		method: 'GET',
		query: tableQuery.value,
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-Site-Hash': selectedSiteHash.value,
		},
	})
		.then((response) => {
			items.value = response;
			tableQuery.value.page = response.page;
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst rezervace.', severity: 'error' });
		})
		.finally(() => { loading.value = false; });
}

async function deleteItem(id: number) {
	loading.value = true;
	const client = useSanctumClient();
	await client('/api/admin/apartment/reservation/' + id, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat rezervaci.', severity: 'error' });
		})
		.finally(() => { loading.value = false; loadItems(); });
}

function updateSort(column: string) {
	if (tableQuery.value.orderBy === column) {
		tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
	} else {
		tableQuery.value.orderBy = column;
		tableQuery.value.orderWay = 'asc';
	}
	loadItems();
}
function updatePage(page: number) {
	tableQuery.value.page = page;
	loadItems();
}

function switchTab(key: 'upcoming' | 'current' | 'past') {
	activeTab.value = key;
	tableQuery.value.page = 1;
	loadItems();
}

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => { tableQuery.value.search = searchString.value; debouncedLoadItems(); });
watch(selectedSiteHash, () => loadItems());

useHead({ title: pageTitle.value });
onMounted(() => loadItems());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div>
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'add', text: 'Přidat rezervaci' }]"
			slug="apartment_reservations"
		/>

		<div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200">
			<button
				v-for="tab in tabs"
				:key="tab.key"
				type="button"
				class="relative px-4 py-3 text-sm font-semibold transition-colors"
				:class="activeTab === tab.key ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900'"
				@click="switchTab(tab.key as 'upcoming' | 'current' | 'past')"
			>
				{{ tab.label }}
				<span
					v-if="activeTab === tab.key"
					class="absolute bottom-0 left-0 h-0.5 w-full bg-indigo-600"
				/>
			</button>
		</div>

		<BaseTable
			:items="items"
			:columns="[
				{ key: 'code', name: 'Kód', type: 'text', width: 80, hidden: false, sortable: false },
				{ key: 'apartment', name: 'Apartmán', type: 'text', width: 80, hidden: false, sortable: false },
				{ key: 'guest_firstname', name: 'Host', type: 'text', width: 80, hidden: false, sortable: false },
				{ key: 'guest_email', name: 'E-mail', type: 'text', width: 80, hidden: true, sortable: false },
				{ key: 'start_date', name: 'Od', type: 'date', width: 80, hidden: false, sortable: true },
				{ key: 'end_date', name: 'Do', type: 'date', width: 80, hidden: false, sortable: true },
				{ key: 'status', name: 'Stav', type: 'status', width: 80, hidden: false, sortable: false },
				{ key: 'total_price', name: 'Cena', type: 'number', width: 80, hidden: false, sortable: false },
			]"
			:actions="[{ type: 'edit' }, { type: 'delete' }]"
			:loading="loading"
			:error="error"
			singular="Rezervace"
			plural="Rezervace"
			:query="tableQuery"
			slug="apartment_reservations"
			@delete-item="deleteItem"
			@update-sort="updateSort"
			@update-page="updatePage"
		/>
	</div>
</template>
