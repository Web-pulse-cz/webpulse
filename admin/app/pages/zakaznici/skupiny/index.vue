<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Skupiny zákazníků');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
	{ name: 'Zákazníci', link: '/zakaznici', current: false },
	{ name: pageTitle.value, link: '/zakaznici/skupiny', current: true },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = ref({ search: null as string | null, paginate: 12, page: 1, orderBy: 'position', orderWay: 'asc' });
const items = ref([]);

async function loadItems() {
	loading.value = true;
	const client = useSanctumClient();
	await client('/api/admin/customer/group', {
		method: 'GET', query: tableQuery.value,
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => { items.value = r; tableQuery.value.page = r.page; })
	.catch(() => { error.value = true; })
	.finally(() => { loading.value = false; });
}

async function deleteItem(id: number) {
	const client = useSanctumClient();
	await client('/api/admin/customer/group/' + id, {
		method: 'DELETE', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).finally(() => { loadItems(); });
}

function updateSort(column: string) {
	if (tableQuery.value.orderBy === column) tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
	else { tableQuery.value.orderBy = column; tableQuery.value.orderWay = 'asc'; }
	loadItems();
}
function updatePage(page: number) { tableQuery.value.page = page; loadItems(); }

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => { tableQuery.value.search = searchString.value; debouncedLoadItems(); });
watch(selectedSiteHash, () => loadItems());

useHead({ title: pageTitle.value });
onMounted(() => { loadItems(); });
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div>
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" :actions="[{ type: 'add', text: 'Přidat skupinu' }]" slug="customers" />
		<BaseTable
			:items="items"
			:columns="[
				{ key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
				{ key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
				{ key: 'discount_value', name: 'Sleva', type: 'number', width: 100, hidden: false, sortable: true },
				{ key: 'discount_type', name: 'Typ slevy', type: 'text', width: 80, hidden: true, sortable: false },
				{ key: 'customers_count', name: 'Zákazníků', type: 'number', width: 100, hidden: false, sortable: false },
			]"
			:actions="[{ type: 'edit' }, { type: 'delete' }]"
			:loading="loading" :error="error"
			singular="Skupina" plural="Skupiny zákazníků"
			:query="tableQuery" slug="customers"
			@delete-item="deleteItem" @update-sort="updateSort" @update-page="updatePage"
		/>
	</div>
</template>
