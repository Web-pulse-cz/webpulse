<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { SunIcon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();
const languageStore = useLanguageStore();
const selectedLocale = ref('cs');
const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové období' : 'Detail období');

const breadcrumbs = ref([
	{ name: 'Roční období', link: '/ubytovani/rocni-obdobi', current: false },
	{ name: pageTitle.value, link: '/ubytovani/rocni-obdobi/pridat', current: true },
]);

const item = ref({
	id: null as number | null,
	is_recurring: true as boolean,
	start_month: null as number | null,
	start_day: null as number | null,
	end_month: null as number | null,
	end_day: null as number | null,
	start_date: '' as string,
	end_date: '' as string,
	color: '#10b981' as string,
	position: 0 as number,
	translations: {} as Record<string, any>,
	sites: [] as number[],
	translateAutomatically: false as boolean,
});

const translatableAttributes = ref([{ field: 'name', label: 'Název' }]);

const monthOptions = [
	{ value: 1, name: 'Leden' }, { value: 2, name: 'Únor' }, { value: 3, name: 'Březen' },
	{ value: 4, name: 'Duben' }, { value: 5, name: 'Květen' }, { value: 6, name: 'Červen' },
	{ value: 7, name: 'Červenec' }, { value: 8, name: 'Srpen' }, { value: 9, name: 'Září' },
	{ value: 10, name: 'Říjen' }, { value: 11, name: 'Listopad' }, { value: 12, name: 'Prosinec' },
];

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/season/' + route.params.id, {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-Site-Hash': selectedSiteHash.value,
		},
	})
		.then((response: any) => {
			item.value = response;
			item.value.sites = response.sites?.map((s: any) => s.id) || [];
			breadcrumbs.value.pop();
			pageTitle.value = item.value.translations?.cs?.name || 'Detail období';
			breadcrumbs.value.push({ name: pageTitle.value, link: '/ubytovani/rocni-obdobi/' + route.params.id, current: true });
			fillEmptyTranslations();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst období.', severity: 'error' });
			router.push('/ubytovani/rocni-obdobi');
		})
		.finally(() => { loading.value = false; });
}

async function saveItem(redirect = true as boolean) {
	if (!(await validateForm())) return;
	const client = useSanctumClient();
	loading.value = true;

	await client(
		route.params.id === 'pridat' ? '/api/admin/season' : '/api/admin/season/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(item.value),
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		},
	)
		.then((response: any) => {
			$toast.show({
				summary: 'Hotovo',
				detail: route.params.id === 'pridat' ? 'Období bylo vytvořeno.' : 'Období bylo upraveno.',
				severity: 'success',
			});
			if (!redirect && route.params.id === 'pridat') router.push('/ubytovani/rocni-obdobi/' + response.id);
			else if (redirect) router.push('/ubytovani/rocni-obdobi');
			else loadItem();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit období.', severity: 'error' });
		})
		.finally(() => { loading.value = false; });
}

function fillEmptyTranslations() {
	languageStore.languages.forEach((language: any) => {
		if (item.value.translations[language.code] === undefined) item.value.translations[language.code] = {};
		translatableAttributes.value.forEach((attribute) => {
			if (item.value.translations[language.code][attribute.field] === undefined) {
				item.value.translations[language.code][attribute.field] = '';
			}
		});
	});
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => { if (route.params.id !== 'pridat') loadItem(); });

onMounted(() => {
	if (route.params.id !== 'pridat') loadItem();
	fillEmptyTranslations();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-24">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			slug="seasons"
			@save="saveItem"
		/>

		<Form ref="formRef" @submit="saveItem">
			<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
				<div class="col-span-1 space-y-8 lg:col-span-9">
					<LayoutContainer>
						<div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
							<div class="flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
									<SunIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Období</LayoutTitle>
							</div>
							<div class="flex items-center gap-2">
								<span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Jazyk:</span>
								<span class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase text-white">{{ selectedLocale }}</span>
							</div>
						</div>

						<div class="grid grid-cols-1 gap-y-6">
							<BaseFormInput
								v-if="item.translations?.[selectedLocale]?.name !== undefined"
								:key="`name-${selectedLocale}`"
								v-model="item.translations[selectedLocale].name"
								label="Název období"
								type="text"
								name="name"
								rules="required|min:2"
								placeholder="Např. Léto"
							/>
							<BaseFormSwitch
								v-model="item.is_recurring"
								label="Opakovat každoročně"
								name="is_recurring"
							/>

							<div v-if="item.is_recurring" class="grid grid-cols-1 gap-y-4 rounded-2xl bg-slate-50 p-6 sm:grid-cols-4 sm:gap-x-4">
								<BaseFormSelect v-model="item.start_month" label="Od měsíce" name="start_month" :options="monthOptions" />
								<BaseFormInput v-model="item.start_day" label="Od dne" type="number" name="start_day" />
								<BaseFormSelect v-model="item.end_month" label="Do měsíce" name="end_month" :options="monthOptions" />
								<BaseFormInput v-model="item.end_day" label="Do dne" type="number" name="end_day" />
							</div>

							<div v-else class="grid grid-cols-1 gap-y-4 rounded-2xl bg-slate-50 p-6 sm:grid-cols-2 sm:gap-x-4">
								<BaseFormInput v-model="item.start_date" label="Od data" type="date" name="start_date" />
								<BaseFormInput v-model="item.end_date" label="Do data" type="date" name="end_date" />
							</div>

							<BaseFormInput v-model="item.color" label="Barva" type="color" name="color" />
						</div>
					</LayoutContainer>
				</div>

				<aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
					<LayoutActionsDetailBlock
						v-model:selected-locale="selectedLocale"
						v-model:translate-automatically="item.translateAutomatically"
						v-model:position="item.position"
						v-model:sites="item.sites"
						:allow-image="false"
						:allow-position="true"
						class="shadow-sm"
					/>
				</aside>
			</div>
		</Form>
	</div>
</template>
