<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { SparklesIcon } from '@heroicons/vue/24/outline';
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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové vybavení' : 'Detail vybavení');

const breadcrumbs = ref([
	{ name: 'Vybavení', link: '/ubytovani/vybaveni', current: false },
	{ name: pageTitle.value, link: '/ubytovani/vybaveni/pridat', current: true },
]);

const item = ref({
	id: null as number | null,
	icon: '' as string,
	position: 0 as number,
	translations: {} as Record<string, any>,
	sites: [] as number[],
	translateAutomatically: false as boolean,
});

const translatableAttributes = ref([{ field: 'name', label: 'Název' }]);

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/amenity/' + route.params.id, {
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
			pageTitle.value = item.value.translations?.cs?.name || 'Detail vybavení';
			breadcrumbs.value.push({ name: pageTitle.value, link: '/ubytovani/vybaveni/' + route.params.id, current: true });
			fillEmptyTranslations();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst vybavení.', severity: 'error' });
			router.push('/ubytovani/vybaveni');
		})
		.finally(() => { loading.value = false; });
}

async function saveItem(redirect = true as boolean) {
	if (!(await validateForm())) return;
	const client = useSanctumClient();
	loading.value = true;

	await client(
		route.params.id === 'pridat' ? '/api/admin/amenity' : '/api/admin/amenity/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(item.value),
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		},
	)
		.then((response: any) => {
			$toast.show({
				summary: 'Hotovo',
				detail: route.params.id === 'pridat' ? 'Vybavení bylo vytvořeno.' : 'Vybavení bylo upraveno.',
				severity: 'success',
			});
			if (!redirect && route.params.id === 'pridat') router.push('/ubytovani/vybaveni/' + response.id);
			else if (redirect) router.push('/ubytovani/vybaveni');
			else loadItem();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit vybavení.', severity: 'error' });
		})
		.finally(() => { loading.value = false; });
}

function fillEmptyTranslations() {
	languageStore.languages.forEach((language: any) => {
		if (item.value.translations[language.code] === undefined) {
			item.value.translations[language.code] = {};
		}
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
			slug="amenities"
			@save="saveItem"
		/>

		<Form ref="formRef" @submit="saveItem">
			<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
				<div class="col-span-1 space-y-8 lg:col-span-9">
					<LayoutContainer>
						<div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
							<div class="flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
									<SparklesIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">Vybavení</LayoutTitle>
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
								label="Název vybavení"
								type="text"
								name="name"
								rules="required|min:2"
								placeholder="Např. Wi-Fi"
							/>
							<BaseFormInput
								v-model="item.icon"
								label="Ikona (např. heroicons název)"
								type="text"
								name="icon"
								placeholder="wifi"
							/>
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
