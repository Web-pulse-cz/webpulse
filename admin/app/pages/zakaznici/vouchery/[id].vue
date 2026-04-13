<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový voucher' : 'Detail voucheru');
const breadcrumbs = ref([
	{ name: 'Zákazníci', link: '/zakaznici', current: false },
	{ name: 'Vouchery', link: '/zakaznici/vouchery', current: false },
	{ name: pageTitle.value, link: '/zakaznici/vouchery/pridat', current: true },
]);

const allCustomers = ref([]);

const item = ref({
	id: null, code: '', name: '', description: '',
	discount_type: 'fixed' as string, discount_value: 0, currency_id: null,
	min_order_value: null as number | null,
	valid_from: '', valid_to: '',
	max_uses: null as number | null, used_count: 0,
	max_uses_per_customer: null as number | null,
	is_active: true, customers: [] as number[],
	sites: [] as number[],
});

const discountTypeOptions = ref([
	{ value: 'fixed', name: 'Pevná částka' },
	{ value: 'percentage', name: 'Procenta (%)' },
]);

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/voucher/' + route.params.id, {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		item.value = {
			...r,
			customers: r.customers?.map((c: any) => c.id) || [],
			sites: r.sites || [],
		};
		pageTitle.value = r.code || 'Voucher';
		breadcrumbs.value[2] = { name: pageTitle.value, link: '/zakaznici/vouchery/' + r.id, current: true };
	}).finally(() => { loading.value = false; });
}

async function loadCustomers() {
	const client = useSanctumClient();
	await client('/api/admin/customer', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		const d = r?.data || r;
		allCustomers.value = d;
	}).catch(() => {});
}

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;
	await client(route.params.id === 'pridat' ? '/api/admin/voucher' : '/api/admin/voucher/' + route.params.id, {
		method: 'POST', body: JSON.stringify(item.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		$toast.show({ summary: 'Hotovo', detail: 'Voucher uložen.', severity: 'success' });
		if (!redirect && route.params.id === 'pridat') router.push('/zakaznici/vouchery/' + r.id);
		else if (redirect) router.push('/zakaznici/vouchery');
		else loadItem();
	}).catch(() => { $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit voucher.', severity: 'error' }); })
	.finally(() => { loading.value = false; });
}

function generateCode() {
	const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	let code = '';
	for (let i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
	item.value.code = code;
}

watch(selectedSiteHash, () => { loadItem(); });
useHead({ title: pageTitle.value });
onMounted(() => {
	loadCustomers();
	if (route.params.id !== 'pridat') loadItem();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" :actions="[{ type: 'save' }, { type: 'save-and-stay' }]" slug="vouchers" @save="saveItem" />
		<Form @submit="saveItem">
			<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
				<div class="col-span-1 space-y-8 lg:col-span-9">
					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><DocumentCurrencyEuroIcon class="size-5" /></div>
							<LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
						</div>
						<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
							<div>
								<BaseFormInput v-model="item.code" label="Kód voucheru" name="code" rules="required" />
								<button type="button" class="mt-1 text-xs font-medium text-indigo-600 hover:text-indigo-500" @click="generateCode">Vygenerovat kód</button>
							</div>
							<BaseFormInput v-model="item.name" label="Název" name="name" />
							<BaseFormTextarea v-model="item.description" label="Popis" name="description" rows="2" class="col-span-full" />
						</div>
					</LayoutContainer>

					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><BanknotesIcon class="size-5" /></div>
							<LayoutTitle class="!mb-0">Sleva</LayoutTitle>
						</div>
						<div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
							<BaseFormSelect v-model="item.discount_type" label="Typ slevy" name="discount_type" :options="discountTypeOptions" />
							<BaseFormInput v-model="item.discount_value" :label="item.discount_type === 'percentage' ? 'Sleva (%)' : 'Sleva (částka)'" type="number" name="discount_value" :step="0.01" />
							<BaseFormSelect v-if="item.discount_type === 'fixed'" v-model="item.currency_id" label="Měna" name="currency_id" :options="currencyStore.currenciesOptions" />
							<BaseFormInput v-model="item.min_order_value" label="Minimální hodnota objednávky" type="number" name="min_order_value" :step="0.01" />
						</div>
					</LayoutContainer>

					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><ClockIcon class="size-5" /></div>
							<LayoutTitle class="!mb-0">Platnost a limity</LayoutTitle>
						</div>
						<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
							<BaseFormInput v-model="item.valid_from" label="Platný od" type="date" name="valid_from" />
							<BaseFormInput v-model="item.valid_to" label="Platný do" type="date" name="valid_to" />
							<BaseFormInput v-model="item.max_uses" label="Max. počet použití celkem" type="number" name="max_uses" placeholder="Neomezeno" />
							<BaseFormInput v-model="item.max_uses_per_customer" label="Max. použití na zákazníka" type="number" name="max_uses_per_customer" placeholder="Neomezeno" />
						</div>
						<div class="mt-4 flex items-center gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
							<span class="text-sm font-medium text-slate-700">Použito:</span>
							<span class="text-lg font-bold text-indigo-600">{{ item.used_count }}x</span>
							<span v-if="item.max_uses" class="text-sm text-slate-400">/ {{ item.max_uses }}</span>
						</div>
					</LayoutContainer>

					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600"><UsersIcon class="size-5" /></div>
							<LayoutTitle class="!mb-0">Přiřazení zákazníci</LayoutTitle>
						</div>
						<p class="mb-4 text-sm text-slate-500">Pokud nevyberete žádného zákazníka, voucher bude platit pro všechny.</p>
						<div class="max-h-60 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-3">
							<label
								v-for="customer in allCustomers"
								:key="customer.id"
								class="flex cursor-pointer items-center gap-3 rounded-lg p-2 transition hover:bg-slate-50"
								:class="item.customers.includes(customer.id) ? 'bg-indigo-50' : ''"
							>
								<input
									type="checkbox"
									:checked="item.customers.includes(customer.id)"
									class="rounded text-indigo-600"
									@change="item.customers.includes(customer.id) ? (item.customers = item.customers.filter((id) => id !== customer.id)) : item.customers.push(customer.id)"
								/>
								<div>
									<span class="text-sm font-medium text-slate-900">{{ customer.full_name }}</span>
									<span v-if="customer.email" class="ml-2 text-xs text-slate-400">{{ customer.email }}</span>
								</div>
							</label>
							<div v-if="!allCustomers.length" class="py-4 text-center text-sm text-slate-400">Žádní zákazníci.</div>
						</div>
					</LayoutContainer>
				</div>

				<div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
					<LayoutContainer class="!py-6">
						<LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">Stav</LayoutTitle>
						<div class="mt-4">
							<BaseFormSwitch v-model:enabled="item.is_active" enabled-text="Aktivní" disabled-text="Neaktivní" />
						</div>
					</LayoutContainer>

					<LayoutContainer v-if="item.code" class="!py-6">
						<LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">Kód</LayoutTitle>
						<p class="mt-2 text-center font-mono text-2xl font-bold tracking-widest text-indigo-600">{{ item.code }}</p>
					</LayoutContainer>

					<LayoutActionsDetailBlock v-model:sites="item.sites" :allow-image="false" :allow-is-active="false" />
				</div>
			</div>
		</Form>
	</div>
</template>
