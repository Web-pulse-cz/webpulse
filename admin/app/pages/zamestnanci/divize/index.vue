<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Divize');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
	{ name: 'Zaměstnanci', link: '/zamestnanci', current: false },
	{ name: pageTitle.value, link: '/zamestnanci/divize', current: true },
]);

const searchString = ref(inject('searchString', ''));
const tableQuery = ref({ search: null as string | null, paginate: 12, page: 1, orderBy: 'position', orderWay: 'asc' });
const items = ref([]);

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();
	await client('/api/admin/employee/division', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => { items.value = { data: r }; })
	.catch(() => { error.value = true; })
	.finally(() => { loading.value = false; });
}

async function deleteItem(id: number) {
	const client = useSanctumClient();
	await client('/api/admin/employee/division/' + id, {
		method: 'DELETE', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then(() => { loadItems(); });
}

function updateSort(column: string) {
	if (tableQuery.value.orderBy === column) tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
	else { tableQuery.value.orderBy = column; tableQuery.value.orderWay = 'asc'; }
	loadItems();
}
function updatePage(page: number) { tableQuery.value.page = page; loadItems(); }

useHead({ title: pageTitle.value });
onMounted(() => { loadItems(); });
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div>
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" :actions="[{ type: 'add', text: 'Přidat divizi' }]" slug="employees" />
		<BaseTable
			:items="items"
			:columns="[
				{ key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
				{ key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
				{ key: 'email', name: 'E-mail', type: 'text', width: 150, hidden: true, sortable: false },
				{ key: 'employees_count', name: 'Zaměstnanců', type: 'number', width: 100, hidden: false, sortable: false },
			]"
			:actions="[{ type: 'edit' }, { type: 'delete' }]"
			:loading="loading" :error="error"
			singular="Divize" plural="Divize"
			:query="tableQuery" slug="employees"
			@delete-item="deleteItem" @update-sort="updateSort" @update-page="updatePage"
		/>
	</div>
</template>
