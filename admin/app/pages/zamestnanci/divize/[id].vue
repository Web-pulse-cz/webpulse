<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const error = ref(false);
const loading = ref(false);
const employees = ref([]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová divize' : 'Detail divize');
const breadcrumbs = ref([
	{ name: 'Zaměstnanci', link: '/zamestnanci', current: false },
	{ name: 'Divize', link: '/zamestnanci/divize', current: false },
	{ name: pageTitle.value, link: '/zamestnanci/divize/pridat', current: true },
]);

const item = ref({
	id: null, name: '', description: '', color: '#6366f1',
	address: '', city: '', zip: '', phone: '', email: '',
	head_employee_id: null, position: 0,
});

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/employee/division/' + route.params.id, {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		item.value = r;
		pageTitle.value = r.name;
		breadcrumbs.value[2] = { name: r.name, link: '/zamestnanci/divize/' + route.params.id, current: true };
	}).catch(() => { error.value = true; })
	.finally(() => { loading.value = false; });
}

async function loadEmployees() {
	const client = useSanctumClient();
	await client('/api/admin/employee', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		const d = r?.data || r;
		employees.value = d.map((e: any) => ({ value: e.id, name: e.full_name || e.first_name + ' ' + e.last_name }));
	}).catch(() => {});
}

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;
	await client(route.params.id === 'pridat' ? '/api/admin/employee/division' : '/api/admin/employee/division/' + route.params.id, {
		method: 'POST', body: JSON.stringify(item.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		$toast.show({ summary: 'Hotovo', detail: route.params.id === 'pridat' ? 'Divize vytvořena.' : 'Divize upravena.', severity: 'success' });
		if (!redirect && route.params.id === 'pridat') router.push('/zamestnanci/divize/' + r.id);
		else if (redirect) router.push('/zamestnanci/divize');
		else loadItem();
	}).catch(() => { $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit divizi.', severity: 'error' }); })
	.finally(() => { loading.value = false; });
}

useHead({ title: pageTitle.value });
onMounted(() => { loadEmployees(); if (route.params.id !== 'pridat') loadItem(); });
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" :actions="[{ type: 'save' }, { type: 'save-and-stay' }]" slug="employees" @save="saveItem" />
		<Form @submit="saveItem">
			<LayoutContainer>
				<LayoutTitle>Údaje divize</LayoutTitle>
				<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
					<BaseFormInput v-model="item.name" label="Název" name="name" rules="required" />
					<BaseFormColorPicker v-model="item.color" label="Barva" name="color" />
					<BaseFormTextarea v-model="item.description" label="Popis" name="description" rows="3" class="col-span-full" />
					<BaseFormInput v-model="item.address" label="Adresa" name="address" />
					<BaseFormInput v-model="item.city" label="Město" name="city" />
					<BaseFormInput v-model="item.zip" label="PSČ" name="zip" />
					<BaseFormInput v-model="item.phone" label="Telefon" name="phone" />
					<BaseFormInput v-model="item.email" label="E-mail" name="email" />
					<BaseFormSelect v-model="item.head_employee_id" label="Vedoucí" name="head_employee_id" :options="employees" />
					<BaseFormInput v-model="item.position" label="Pořadí" type="number" name="position" />
				</div>
			</LayoutContainer>
		</Form>
	</div>
</template>
