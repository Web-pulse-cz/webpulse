<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

import { useCountryStore } from '~/../stores/countryStore';

const { $toast } = useNuxtApp();
const countryStore = useCountryStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
	{ name: 'Základní údaje', link: '#info', current: false },
	{ name: 'Adresy', link: '#adresy', current: false },
	{ name: 'Bankovní údaje', link: '#banka', current: false },
	{ name: 'Faktury', link: '#faktury', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový klient' : 'Detail klienta');

const breadcrumbs = ref([
	{
		name: 'Klienti',
		link: '/klienti',
		current: false,
	},
	{
		name: pageTitle.value,
		link: '/klienti/pridat',
		current: true,
	},
]);

const item = ref({
	id: null,
	type: 'customer',
	name: '',
	full_name: '',
	email: '',
	email_copy: '',
	phone_prefix: '+420',
	phone: '',
	ico: '',
	dic: '',
	web: '',
	street: '',
	city: '',
	zip: '',
	country_id: null,
	has_delivery_address: false,
	delivery_name: '',
	delivery_street: '',
	delivery_city: '',
	delivery_zip: '',
	delivery_country_id: null,
	bank_account_number: '',
	bank_account_iban: '',
	bank_account_swift: '',
	variable_symbol: '',
	note: '',
});

const clientInvoices = ref([]);

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;

	await client('/api/admin/client/' + route.params.id, {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then((response) => {
			item.value = response;
			pageTitle.value = item.value.name;
			breadcrumbs.value[1] = {
				name: pageTitle.value,
				link: '/klienti/' + route.params.id,
				current: true,
			};
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se načíst klienta.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

async function loadClientInvoices() {
	const client = useSanctumClient();

	await client('/api/admin/invoice', {
		method: 'GET',
		query: { client_id: route.params.id },
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then((response) => {
			clientInvoices.value = response?.data || response;
		})
		.catch(() => {});
}

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;

	await client(
		route.params.id === 'pridat' ? '/api/admin/client' : '/api/admin/client/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(item.value),
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
		},
	)
		.then((response) => {
			$toast.show({
				summary: 'Hotovo',
				detail: route.params.id === 'pridat' ? 'Klient byl úspěšně vytvořen.' : 'Klient byl úspěšně upraven.',
				severity: 'success',
			});
			if (!redirect && route.params.id === 'pridat') {
				router.push('/klienti/' + response.id);
			} else if (redirect) {
				router.push('/klienti');
			} else {
				loadItem();
			}
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se uložit klienta. Zkontrolujte vyplněná pole a zkuste to znovu.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

const typeOptions = ref([
	{ value: 'customer', name: 'Odběratel' },
	{ value: 'supplier', name: 'Dodavatel' },
	{ value: 'both', name: 'Obojí' },
]);

watchEffect(() => {
	const routeTabHash = route.hash;
	if (routeTabHash && routeTabHash !== '') {
		tabs.value.forEach((tab) => {
			tab.current = tab.link === routeTabHash;
		});
	} else {
		tabs.value[0].current = true;
		router.push(route.path + '#info');
	}
});

useHead({
	title: pageTitle.value,
});

onMounted(() => {
	if (route.params.id !== 'pridat') {
		loadItem();
		loadClientInvoices();
	}
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
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			:modify-bottom="false"
			slug="clients"
			@save="saveItem"
		/>

		<LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

		<Form @submit="saveItem">
			<template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
				<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
					<div class="col-span-1 space-y-8 lg:col-span-9">
						<LayoutContainer>
							<div class="mb-6 flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
									<UserIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
							</div>

							<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
								<BaseFormInput
									v-model="item.name"
									label="Název / Jméno"
									type="text"
									name="name"
									rules="required|min:2"
									class="col-span-full"
								/>

								<BaseFormInput
									v-model="item.full_name"
									label="Celý název"
									type="text"
									name="full_name"
								/>

								<BaseFormSelect
									v-model="item.type"
									label="Typ"
									name="type"
									:options="typeOptions"
								/>

								<BaseFormInput
									v-model="item.email"
									label="E-mail"
									type="email"
									name="email"
								/>

								<BaseFormInput
									v-model="item.email_copy"
									label="Kopie e-mailu"
									type="email"
									name="email_copy"
								/>

								<div class="col-span-full flex gap-3 sm:col-span-1">
									<BaseFormInput
										v-model="item.phone_prefix"
										label="Předčíslí"
										name="phone_prefix"
										class="w-24"
									/>
									<BaseFormInput
										v-model="item.phone"
										label="Telefon"
										name="phone"
										class="flex-1"
									/>
								</div>

								<BaseFormInput
									v-model="item.web"
									label="Web"
									type="text"
									name="web"
								/>

								<div class="col-span-full grid grid-cols-2 gap-6 border-t border-slate-100 pt-4">
									<BaseFormInput
										v-model="item.ico"
										label="IČO"
										name="ico"
									/>
									<BaseFormInput
										v-model="item.dic"
										label="DIČ"
										name="dic"
									/>
								</div>
							</div>
						</LayoutContainer>

						<LayoutContainer>
							<LayoutTitle>Poznámka</LayoutTitle>
							<BaseFormTextarea
								v-model="item.note"
								label=""
								name="note"
								rows="4"
							/>
						</LayoutContainer>
					</div>

					<div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
						<LayoutContainer v-if="item.synced_at" class="!py-6">
							<LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">Fakturoid</LayoutTitle>
							<div class="mt-2 text-xs text-slate-500">
								<p>ID: {{ item.fakturoid_id || '—' }}</p>
								<p class="mt-1">Synced: {{ item.synced_at ? new Date(item.synced_at).toLocaleString() : '—' }}</p>
							</div>
						</LayoutContainer>
					</div>
				</div>
			</template>

			<template v-if="tabs.find((tab) => tab.current && tab.link === '#adresy')">
				<div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
					<LayoutContainer>
						<LayoutTitle>Fakturační adresa</LayoutTitle>
						<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
							<BaseFormInput
								v-model="item.street"
								label="Ulice a č. p."
								name="street"
								class="col-span-full"
							/>
							<BaseFormInput v-model="item.zip" label="PSČ" name="zip" />
							<BaseFormInput v-model="item.city" label="Město" name="city" />
							<BaseFormSelect
								v-model="item.country_id"
								label="Země"
								name="country_id"
								class="col-span-full"
								:options="countryStore.countriesOptions"
							/>

							<div class="col-span-full mt-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
								<BaseFormCheckbox
									v-model="item.has_delivery_address"
									label="Má odlišnou doručovací adresu"
									name="has_delivery_address"
									class="flex-row-reverse justify-between"
								/>
							</div>
						</div>
					</LayoutContainer>

					<Transition
						enter-active-class="transition duration-300 ease-out"
						enter-from-class="opacity-0 translate-x-4"
						enter-to-class="opacity-100 translate-x-0"
					>
						<LayoutContainer v-if="item.has_delivery_address">
							<LayoutTitle>Doručovací adresa</LayoutTitle>
							<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
								<BaseFormInput
									v-model="item.delivery_name"
									label="Jméno / Firma"
									name="delivery_name"
									class="col-span-full"
								/>
								<BaseFormInput
									v-model="item.delivery_street"
									label="Ulice a č. p."
									name="delivery_street"
									class="col-span-full"
								/>
								<BaseFormInput v-model="item.delivery_zip" label="PSČ" name="delivery_zip" />
								<BaseFormInput v-model="item.delivery_city" label="Město" name="delivery_city" />
								<BaseFormSelect
									v-model="item.delivery_country_id"
									label="Země"
									name="delivery_country_id"
									class="col-span-full"
									:options="countryStore.countriesOptions"
								/>
							</div>
						</LayoutContainer>
					</Transition>
				</div>
			</template>

			<template v-if="tabs.find((tab) => tab.current && tab.link === '#banka')">
				<LayoutContainer>
					<LayoutTitle>Bankovní údaje</LayoutTitle>
					<div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
						<BaseFormInput
							v-model="item.bank_account_number"
							label="Číslo účtu"
							name="bank_account_number"
						/>
						<BaseFormInput
							v-model="item.bank_account_iban"
							label="IBAN"
							name="bank_account_iban"
						/>
						<BaseFormInput
							v-model="item.bank_account_swift"
							label="SWIFT/BIC"
							name="bank_account_swift"
						/>
						<BaseFormInput
							v-model="item.variable_symbol"
							label="Variabilní symbol"
							name="variable_symbol"
						/>
					</div>
				</LayoutContainer>
			</template>

			<template v-if="tabs.find((tab) => tab.current && tab.link === '#faktury')">
				<LayoutContainer>
					<LayoutTitle>Faktury klienta</LayoutTitle>
					<div v-if="clientInvoices.length === 0" class="py-8 text-center text-sm text-slate-400">
						Tento klient zatím nemá žádné faktury.
					</div>
					<div v-else class="divide-y divide-slate-100">
						<NuxtLink
							v-for="inv in clientInvoices"
							:key="inv.id"
							:to="'/faktury/' + inv.id"
							class="flex items-center justify-between px-2 py-3 hover:bg-slate-50 rounded-lg transition"
						>
							<div>
								<span class="font-medium text-slate-900">{{ inv.number || '—' }}</span>
								<span class="ml-3 text-sm text-slate-500">{{ inv.subject }}</span>
							</div>
							<div class="flex items-center gap-4">
								<span class="text-sm font-medium">{{ inv.total }} </span>
								<span
									class="rounded-full px-2 py-0.5 text-xs font-medium"
									:class="{
										'bg-green-50 text-green-700': inv.status === 'paid',
										'bg-yellow-50 text-yellow-700': inv.status === 'sent',
										'bg-red-50 text-red-700': inv.status === 'overdue',
										'bg-slate-50 text-slate-700': inv.status === 'open',
										'bg-gray-50 text-gray-500': inv.status === 'cancelled',
									}"
								>
									{{ inv.status }}
								</span>
							</div>
						</NuxtLink>
					</div>
				</LayoutContainer>
			</template>
		</Form>
	</div>
</template>
