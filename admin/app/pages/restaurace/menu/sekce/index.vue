<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Sekce jídelního lístku');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
	{ name: 'Jídelní lístky', link: '/restaurace/menu', current: false },
	{ name: pageTitle.value, link: '/restaurace/menu/sekce', current: true },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tableQuery = ref({
	search: null as string | null,
	paginate: 12 as number,
	page: 1 as number,
	orderBy: 'position' as string,
	orderWay: 'asc' as string,
});

const items = ref([]);

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();

	await client('/api/admin/food/menu-section', {
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
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se načíst sekce jídelního lístku.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

async function deleteItem(id: number) {
	loading.value = true;
	const client = useSanctumClient();

	await client('/api/admin/food/menu-section/' + id, {
		method: 'DELETE',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se smazat sekci.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
			loadItems();
		});
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

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => {
	tableQuery.value.search = searchString.value;
	debouncedLoadItems();
});

watch(selectedSiteHash, () => loadItems());

useHead({
	title: pageTitle.value,
});

onMounted(() => {
	loadItems();
});
definePageMeta({
	middleware: 'sanctum:auth',
});
</script>

<template>
	<div>
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'add', text: 'Přidat sekci' }]"
			slug="menus"
		/>
		<BaseTable
			:items="items"
			:columns="[
				{ key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
				{ key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
				{
					key: 'description',
					name: 'Popis',
					type: 'text',
					width: 200,
					hidden: true,
					sortable: false,
				},
				{
					key: 'position',
					name: 'Pořadí',
					type: 'number',
					width: 80,
					hidden: false,
					sortable: true,
				},
			]"
			:actions="[{ type: 'edit' }, { type: 'delete' }]"
			:loading="loading"
			:error="error"
			singular="Sekce"
			plural="Sekce jídelního lístku"
			:query="tableQuery"
			slug="menus"
			@delete-item="deleteItem"
			@update-sort="updateSort"
			@update-page="updatePage"
		/>
	</div>
</template>
