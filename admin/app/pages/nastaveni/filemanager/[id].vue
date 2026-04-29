<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import { Form } from 'vee-validate';
import { PhotoIcon, ArrowsPointingOutIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const isNew = computed(() => route.params.id === 'pridat');
const pageTitle = ref(isNew.value ? 'Nový formát' : 'Detail formátu');

const breadcrumbs = ref([
	{ name: 'Filemanager', link: '/nastaveni/filemanager', current: false },
	{ name: pageTitle.value, link: '/nastaveni/filemanager/pridat', current: true },
]);

const item = ref({
	id: null as number | null,
	entity_type: '' as string,
	format: '' as string,
	width: null as number | null,
	height: null as number | null,
	mode: 'cover' as string,
	crop_position: 'center' as string,
	path: '' as string,
	position: 0 as number,
	sites: [] as number[],
});

const entityTypeOptions = [
	{ value: 'service', name: 'Služby' },
	{ value: 'user', name: 'Uživatelé' },
	{ value: 'novelty', name: 'Novinky' },
	{ value: 'post', name: 'Blog' },
	{ value: 'post_category', name: 'Kategorie blogu' },
	{ value: 'logo', name: 'Loga' },
	{ value: 'event', name: 'Události' },
	{ value: 'career', name: 'Pracovní pozice' },
	{ value: 'quiz', name: 'Kvízy' },
	{ value: 'apartment', name: 'Apartmány' },
	{ value: 'apartment-type', name: 'Typy apartmánů' },
	{ value: 'building', name: 'Budovy' },
];

const modeOptions = [
	{ value: 'cover', name: 'Cover (vyplnit a oříznout)' },
	{ value: 'contain', name: 'Contain (vejde se celý, doplnění pozadím)' },
	{ value: 'stretch', name: 'Stretch (roztáhnout bez ohledu na poměr)' },
];

const cropPositionOptions = [
	{ value: 'top-left', name: 'Vlevo nahoře' },
	{ value: 'top', name: 'Nahoře' },
	{ value: 'top-right', name: 'Vpravo nahoře' },
	{ value: 'left', name: 'Vlevo' },
	{ value: 'center', name: 'Střed' },
	{ value: 'right', name: 'Vpravo' },
	{ value: 'bottom-left', name: 'Vlevo dole' },
	{ value: 'bottom', name: 'Dole' },
	{ value: 'bottom-right', name: 'Vpravo dole' },
];

const allSites = ref<{ id: number; name: string; hash: string }[]>([]);

async function loadSites() {
	const client = useSanctumClient();
	await client('/api/admin/site', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	})
		.then((response: any) => {
			const data = Array.isArray(response) ? response : response.data || [];
			allSites.value = data.map((s: any) => ({ id: s.id, name: s.name, hash: s.hash }));
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst weby.', severity: 'error' });
		});
}

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;

	await client('/api/admin/filemanager/' + route.params.id, {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-Site-Hash': selectedSiteHash.value,
		},
	})
		.then((response: any) => {
			item.value = {
				...response,
				sites: (response.sites || []).map((s: any) => s.id),
			};
			breadcrumbs.value.pop();
			pageTitle.value = `${item.value.entity_type} / ${item.value.format}`;
			breadcrumbs.value.push({
				name: pageTitle.value,
				link: '/nastaveni/filemanager/' + route.params.id,
				current: true,
			});
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst formát.', severity: 'error' });
			router.push('/nastaveni/filemanager');
		})
		.finally(() => {
			loading.value = false;
		});
}

async function saveItem(redirect = true as boolean) {
	if (!(await validateForm())) return;
	const client = useSanctumClient();
	loading.value = true;

	await client(
		isNew.value ? '/api/admin/filemanager' : '/api/admin/filemanager/' + route.params.id,
		{
			method: 'POST',
			body: JSON.stringify(item.value),
			headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
		},
	)
		.then((response: any) => {
			$toast.show({
				summary: 'Hotovo',
				detail: isNew.value ? 'Formát byl vytvořen.' : 'Formát byl upraven.',
				severity: 'success',
			});
			if (!redirect && isNew.value) router.push('/nastaveni/filemanager/' + response.id);
			else if (redirect) router.push('/nastaveni/filemanager');
			else loadItem();
		})
		.catch(() => {
			error.value = true;
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit formát.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
		});
}

useHead({ title: pageTitle.value });

onMounted(async () => {
	await loadSites();
	if (!isNew.value) await loadItem();
});

definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-24">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			slug="filemanagers"
			@save="saveItem"
		/>

		<Form ref="formRef" @submit="saveItem">
			<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
				<div class="col-span-1 space-y-8 lg:col-span-9">
					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
								<PhotoIcon class="size-5" />
							</div>
							<LayoutTitle class="!mb-0">Definice formátu</LayoutTitle>
						</div>

						<div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
							<BaseFormSelect
								v-model="item.entity_type"
								label="Typ entity"
								name="entity_type"
								rules="required"
								:options="entityTypeOptions"
							/>
							<BaseFormInput
								v-model="item.format"
								label="Formát (klíč)"
								type="text"
								name="format"
								rules="required"
								placeholder="small, medium, large..."
							/>
							<BaseFormInput
								v-model="item.width"
								label="Šířka (px)"
								type="number"
								name="width"
							/>
							<BaseFormInput
								v-model="item.height"
								label="Výška (px)"
								type="number"
								name="height"
							/>
							<BaseFormInput
								v-model="item.path"
								label="Adresář (subfolder)"
								type="text"
								name="path"
								rules="required"
								placeholder="small"
							/>
							<BaseFormInput
								v-model="item.position"
								label="Pořadí"
								type="number"
								name="position"
							/>
						</div>
					</LayoutContainer>

					<LayoutContainer>
						<div class="mb-6 flex items-center gap-3">
							<div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
								<ArrowsPointingOutIcon class="size-5" />
							</div>
							<LayoutTitle class="!mb-0">Ořez a přizpůsobení</LayoutTitle>
						</div>

						<div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
							<BaseFormSelect
								v-model="item.mode"
								label="Mód"
								name="mode"
								rules="required"
								:options="modeOptions"
							/>
							<BaseFormSelect
								v-model="item.crop_position"
								label="Pozice ořezu"
								name="crop_position"
								rules="required"
								:options="cropPositionOptions"
							/>
						</div>

						<div class="mt-6 rounded-2xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
							<p><strong>Cover</strong> — obrázek vyplní celý rámec a ořeže se podle pozice (žádné bílé pozadí).</p>
							<p><strong>Contain</strong> — celý obrázek se vejde, prázdné místo se doplní pozadím.</p>
							<p><strong>Stretch</strong> — obrázek se roztáhne bez ohledu na poměr stran.</p>
						</div>
					</LayoutContainer>
				</div>

				<aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
					<LayoutContainer>
						<LayoutTitle class="!mb-4 text-base">Přiřazení k webům</LayoutTitle>
						<div v-if="allSites.length === 0" class="text-sm italic text-slate-400">
							Žádné weby k dispozici.
						</div>
						<div v-else class="space-y-2">
							<label
								v-for="site in allSites"
								:key="site.id"
								class="flex cursor-pointer items-center gap-3 rounded-lg p-2 hover:bg-slate-50"
							>
								<input
									type="checkbox"
									:value="site.id"
									:checked="item.sites.includes(site.id)"
									class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
									@change="
										item.sites.includes(site.id)
											? (item.sites = item.sites.filter((id: number) => id !== site.id))
											: item.sites.push(site.id)
									"
								/>
								<span class="text-sm font-medium text-slate-700">{{ site.name }}</span>
							</label>
						</div>
					</LayoutContainer>
				</aside>
			</div>
		</Form>
	</div>
</template>
