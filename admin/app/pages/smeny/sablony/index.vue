<script setup lang="ts">
import { ref, inject } from 'vue';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Šablony směn');
const loading = ref(false);
const error = ref(false);
const items = ref([]);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const breadcrumbs = ref([
	{ name: 'Směny', link: '/smeny', current: false },
	{ name: pageTitle.value, link: '/smeny/sablony', current: true },
]);

const tableQuery = ref({
	search: null as string | null,
	paginate: 12,
	page: 1,
	orderBy: 'name',
	orderWay: 'asc',
});

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();
	await client('/api/admin/shift/template', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	})
		.then((r) => {
			const d = Array.isArray(r) ? r : (r?.data || []);
			items.value = {
				data: d,
				total: d.length,
				perPage: d.length || 1,
				currentPage: 1,
				lastPage: 1,
			};
		})
		.catch(() => {
			error.value = true;
		})
		.finally(() => {
			loading.value = false;
		});
}

async function deleteItem(id: number) {
	const client = useSanctumClient();
	await client('/api/admin/shift/template/' + id, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => {
		loadItems();
	});
}

function updateSort(column: string) {
	if (tableQuery.value.orderBy === column)
		tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
	else {
		tableQuery.value.orderBy = column;
		tableQuery.value.orderWay = 'asc';
	}
	loadItems();
}
function updatePage(page: number) {
	tableQuery.value.page = page;
	loadItems();
}

watch(selectedSiteHash, () => loadItems());

useHead({ title: pageTitle.value });
onMounted(() => {
	loadItems();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div>
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'add', text: 'Přidat šablonu' }]"
			slug="shifts"
		/>
		<BaseTable
			:items="items"
			:columns="[
				{ key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: false },
				{ key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: false },
				{ key: 'start_time', name: 'Od', type: 'text', width: 80, hidden: false, sortable: false },
				{ key: 'end_time', name: 'Do', type: 'text', width: 80, hidden: false, sortable: false },
				{
					key: 'break_minutes',
					name: 'Přestávka (min)',
					type: 'number',
					width: 100,
					hidden: true,
					sortable: false,
				},
			]"
			:actions="[{ type: 'edit' }, { type: 'delete' }]"
			:loading="loading"
			:error="error"
			singular="Šablona"
			plural="Šablony směn"
			:query="tableQuery"
			slug="shifts"
			@delete-item="deleteItem"
			@update-sort="updateSort"
			@update-page="updatePage"
		/>
	</div>
</template>
