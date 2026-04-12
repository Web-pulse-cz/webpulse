<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const languageStore = useLanguageStore();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);
const selectedLocale = ref('cs');

const tabs = ref([
	{ name: 'Jídelní lístek', link: '#listek', current: false },
	{ name: 'SEO a texty', link: '#seo', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový jídelní lístek' : 'Detail jídelního lístku');
const breadcrumbs = ref([
	{ name: 'Jídelní lístky', link: '/restaurace/menu', current: false },
	{ name: pageTitle.value, link: '/restaurace/menu/pridat', current: true },
]);

const translatableAttributes = ref([
	{ field: 'name', label: 'Název' },
	{ field: 'slug', label: 'Slug' },
	{ field: 'perex', label: 'Perex' },
	{ field: 'text', label: 'Popis' },
	{ field: 'meta_title', label: 'Meta title' },
	{ field: 'meta_description', label: 'Meta popis' },
]);

const sections = ref([]);
const meals = ref([]);
const allergens = ref([]);

const item = ref({
	id: null,
	translations: {} as Record<string, any>,
	items: [] as any[],
	sites: [] as number[],
});

// ─── Loaders ───────────────────────────────────────────────

async function loadItem() {
	const client = useSanctumClient();
	loading.value = true;
	await client('/api/admin/food/menu/' + route.params.id, {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		item.value = r;
		item.value.sites = r.sites?.map((s: any) => s.id) || [];
		if (!item.value.items) item.value.items = [];
		pageTitle.value = r.name || 'Jídelní lístek';
		breadcrumbs.value[1] = { name: pageTitle.value, link: '/restaurace/menu/' + route.params.id, current: true };
		fillEmptyTranslations();
	}).catch(() => {
		error.value = true;
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst jídelní lístek.', severity: 'error' });
	}).finally(() => { loading.value = false; });
}

async function loadSections() {
	const client = useSanctumClient();
	await client('/api/admin/food/menu-section', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		sections.value = r?.data || r;
	}).catch(() => {});
}

async function loadMeals() {
	const client = useSanctumClient();
	await client('/api/admin/food/meal', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		meals.value = r?.data || r;
	}).catch(() => {});
}

async function loadAllergens() {
	const client = useSanctumClient();
	await client('/api/admin/food/allergen', {
		method: 'GET',
		headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	}).then((r) => {
		allergens.value = r?.data || r;
	}).catch(() => {});
}

// ─── Save ────────────────────────────────��─────────────────

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;
	const payload = {
		...item.value,
		items: item.value.items.filter((i: any) => !i._placeholder && i.name),
	};
	await client(route.params.id === 'pridat' ? '/api/admin/food/menu' : '/api/admin/food/menu/' + route.params.id, {
		method: 'POST',
		body: JSON.stringify(payload),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		$toast.show({ summary: 'Hotovo', detail: 'Jídelní lístek uložen.', severity: 'success' });
		if (!redirect && route.params.id === 'pridat') router.push('/restaurace/menu/' + r.id);
		else if (redirect) router.push('/restaurace/menu');
		else loadItem();
	}).catch(() => {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit.', severity: 'error' });
	}).finally(() => { loading.value = false; });
}

// ─── Sections on this menu ─────────────────────────────────

const addSectionId = ref(null as number | null);

// Sections that are already used on this menu
const usedSectionIds = computed(() => {
	const ids = new Set<number>();
	for (const it of item.value.items) {
		if (it.section_id) ids.add(it.section_id);
	}
	return ids;
});

// Sections available to add (not yet used)
const availableSections = computed(() => {
	return sections.value.filter((s: any) => !usedSectionIds.value.has(s.id));
});

// Sections that have items, ordered
const activeSections = computed(() => {
	const sectionMap: Record<number, { id: number; name: string; items: any[] }> = {};
	for (const it of item.value.items) {
		const sid = it.section_id || 0;
		if (!sectionMap[sid]) {
			const sec = sections.value.find((s: any) => s.id === sid);
			sectionMap[sid] = { id: sid, name: sec?.name || 'Bez sekce', items: [] };
		}
		sectionMap[sid].items.push(it);
	}
	return Object.values(sectionMap);
});

function addSection() {
	if (!addSectionId.value) return;
	// Add a placeholder item so the section appears
	item.value.items.push({
		section_id: addSectionId.value, meal_id: null, name: '', description: '',
		price: 0, weight: '', allergen_ids: [], position: item.value.items.length,
		_placeholder: true,
	});
	addSectionId.value = null;
}

function removeSection(sectionId: number) {
	item.value.items = item.value.items.filter((it: any) => it.section_id !== sectionId);
}

// ─── Menu Items within sections ────────────────────────────

function addItemToSection(sectionId: number) {
	item.value.items.push({
		section_id: sectionId, meal_id: null, name: '', description: '',
		price: 0, weight: '', allergen_ids: [], position: item.value.items.length,
	});
}

function removeItem(index: number) {
	// Find the actual index in items array
	item.value.items.splice(index, 1);
}

function removeItemFromSection(sectionId: number, itemIndex: number) {
	const sectionItems = item.value.items.filter((it: any) => it.section_id === sectionId);
	const target = sectionItems[itemIndex];
	if (target) {
		const globalIndex = item.value.items.indexOf(target);
		if (globalIndex !== -1) item.value.items.splice(globalIndex, 1);
	}
}

const mealSearch = ref('');
const showMealDropdown = ref('');

function filteredMeals() {
	if (!mealSearch.value) return meals.value.slice(0, 20);
	const q = mealSearch.value.toLowerCase();
	return meals.value.filter((m: any) => m.name?.toLowerCase().includes(q)).slice(0, 20);
}

function selectMealForItem(sectionId: number, itemIndex: number, meal: any) {
	const sectionItems = item.value.items.filter((it: any) => it.section_id === sectionId);
	const target = sectionItems[itemIndex];
	if (target) {
		target.meal_id = meal.id;
		target.name = meal.name || '';
		target.price = meal.price || 0;
		target.weight = meal.weight || '';
		target._placeholder = false;
		if (meal.allergens?.length) {
			target.allergen_ids = meal.allergens.map((a: any) => a.id);
		}
	}
	mealSearch.value = '';
	showMealDropdown.value = '';
}

function clearMealFromItem(sectionId: number, itemIndex: number) {
	const sectionItems = item.value.items.filter((it: any) => it.section_id === sectionId);
	const target = sectionItems[itemIndex];
	if (target) {
		target.meal_id = null;
		target.name = '';
		target.price = 0;
		target.weight = '';
		target.allergen_ids = [];
	}
}

function toggleAllergen(sectionId: number, itemIndex: number, allergenId: number) {
	const sectionItems = item.value.items.filter((it: any) => it.section_id === sectionId);
	const target = sectionItems[itemIndex];
	if (!target) return;
	const ids = target.allergen_ids || [];
	if (ids.includes(allergenId)) {
		target.allergen_ids = ids.filter((id: number) => id !== allergenId);
	} else {
		target.allergen_ids = [...ids, allergenId];
	}
}

async function downloadPdf() {
	const client = useSanctumClient();
	try {
		const res = await client.raw('/api/admin/food/menu/' + route.params.id + '/pdf', {
			method: 'GET',
			credentials: 'include',
			responseType: 'blob',
		});
		if (!res.ok) throw new Error('Chyba při stahování');
		const blob = res._data as Blob;
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = 'jidelni-listek-' + route.params.id + '.pdf';
		document.body.appendChild(a);
		a.click();
		a.remove();
		URL.revokeObjectURL(url);
	} catch (e) {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout PDF.', severity: 'error' });
	}
}

// ─── Lifecycle ─────────────────────────────────────────────

function fillEmptyTranslations() {
	languageStore.languages.forEach((language) => {
		if (!item.value.translations[language.code]) {
			item.value.translations[language.code] = {};
			translatableAttributes.value.forEach((attr) => {
				if (item.value.translations[language.code][attr.field] === undefined) {
					item.value.translations[language.code][attr.field] = '';
				}
			});
		}
	});
}

watchEffect(() => {
	const h = route.hash;
	if (h) tabs.value.forEach((t) => { t.current = t.link === h; });
	else { tabs.value[0].current = true; router.push(route.path + '#listek'); }
});

watch(selectedSiteHash, () => { loadItem(); });

useHead({ title: pageTitle.value });

onMounted(() => {
	loadSections(); loadMeals(); loadAllergens();
	if (route.params.id !== 'pridat') {
		loadItem();
	} else {
		fillEmptyTranslations();
	}
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-24">
		<LayoutHeader
			:title="pageTitle"
			:breadcrumbs="breadcrumbs"
			:actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
			slug="menus"
			@save="saveItem"
		/>

		<LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

		<Form @submit="saveItem">
			<!-- ═══ Tab: Jídelní lístek ═══ -->
			<template v-if="tabs.find((t) => t.current && t.link === '#listek')">
				<div class="space-y-6">
					<!-- Add section + PDF export -->
					<LayoutContainer class="!py-4">
						<div class="flex items-center justify-between">
							<div class="flex items-center gap-3">
								<BaseFormSelect
									v-model="addSectionId"
									label=""
									name="add_section"
									:options="availableSections.map((s) => ({ value: s.id, name: s.name }))"
									class="w-56 !mb-0"
								/>
								<button
									type="button"
									class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition"
									:disabled="!addSectionId"
									:class="{ 'opacity-50 cursor-not-allowed': !addSectionId }"
									@click="addSection"
								>
									Přidat sekci
								</button>
							</div>
							<button
								v-if="route.params.id !== 'pridat'"
								type="button"
								class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600 transition"
								@click="downloadPdf"
							>
								Export PDF
							</button>
						</div>
					</LayoutContainer>

					<!-- Empty state -->
					<div v-if="!activeSections.length" class="py-16 text-center">
						<p class="text-sm text-slate-400">Jídelní lístek je prázdný. Přidejte první sekci.</p>
					</div>

					<!-- Sections with items -->
					<LayoutContainer
						v-for="section in activeSections"
						:key="section.id"
					>
						<!-- Section header -->
						<div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-4">
							<div class="flex items-center gap-3">
								<div class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
									<FolderIcon class="size-5" />
								</div>
								<LayoutTitle class="!mb-0">{{ section.name }}</LayoutTitle>
							</div>
							<div class="flex items-center gap-2">
								<button
									type="button"
									class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500 transition"
									@click="addItemToSection(section.id)"
								>
									Přidat jídlo
								</button>
								<button
									type="button"
									class="rounded-lg p-1.5 text-slate-400 ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-500 hover:ring-red-200"
									@click="removeSection(section.id)"
								>
									<TrashIcon class="size-4" />
								</button>
							</div>
						</div>

						<!-- Items in this section -->
						<div v-if="section.items.filter((i) => !i._placeholder || section.items.length === 1).length === 0 || (section.items.length === 1 && section.items[0]._placeholder)" class="py-6 text-center text-sm text-slate-400">
							Sekce je prázdná. Klikněte na „Přidat jídlo".
						</div>

						<div class="space-y-4">
							<div
								v-for="(menuItem, idx) in section.items.filter((i) => !i._placeholder)"
								:key="idx"
								class="rounded-xl bg-slate-50 p-5 ring-1 ring-inset ring-slate-200"
							>
								<!-- Meal autocomplete -->
								<div class="mb-4">
									<div v-if="menuItem.meal_id" class="flex items-center justify-between rounded-lg bg-indigo-50 px-4 py-2.5 ring-1 ring-indigo-200">
										<div>
											<span class="text-sm font-medium text-indigo-900">{{ menuItem.name }}</span>
											<span v-if="menuItem.weight" class="ml-2 text-xs text-indigo-600">{{ menuItem.weight }}</span>
											<span class="ml-2 text-xs text-indigo-500">{{ menuItem.price }} Kč</span>
										</div>
										<button type="button" class="text-xs font-medium text-red-600 hover:text-red-500" @click="clearMealFromItem(section.id, idx)">Odebrat</button>
									</div>

									<div v-else class="relative">
										<label class="mb-1.5 block text-sm font-medium text-slate-700">Vybrat jídlo</label>
										<input
											type="text"
											class="block w-full rounded-xl border-0 bg-white py-2.5 pl-4 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
											placeholder="Vyhledejte jídlo nebo zadejte vlastní..."
											:value="mealSearch"
											@focus="showMealDropdown = section.id + '-' + idx; mealSearch = '';"
											@input="(e) => { mealSearch = (e.target as HTMLInputElement).value; showMealDropdown = section.id + '-' + idx; }"
										/>
										<div
											v-if="showMealDropdown === section.id + '-' + idx"
											class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl bg-white shadow-lg ring-1 ring-slate-200"
										>
											<button
												v-for="meal in filteredMeals()"
												:key="meal.id"
												type="button"
												class="flex w-full items-center justify-between px-4 py-2 text-left text-sm hover:bg-indigo-50 transition"
												@mousedown.prevent="selectMealForItem(section.id, idx, meal)"
											>
												<span class="font-medium text-slate-900">{{ meal.name }}</span>
												<span class="text-xs text-slate-400">{{ meal.price }} Kč</span>
											</button>
											<div v-if="!filteredMeals().length" class="p-3 text-center text-xs text-slate-400">Žádné jídlo nenalezeno.</div>
										</div>
									</div>
								</div>

								<!-- Editable fields -->
								<div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
									<BaseFormInput v-model="menuItem.name" label="Název" :name="'name_' + section.id + '_' + idx" class="sm:col-span-5" />
									<BaseFormInput v-model="menuItem.price" label="Cena (Kč)" type="number" :name="'price_' + section.id + '_' + idx" :step="1" class="sm:col-span-2" />
									<BaseFormInput v-model="menuItem.weight" label="Gramáž" :name="'weight_' + section.id + '_' + idx" placeholder="200g" class="sm:col-span-2" />
									<BaseFormInput v-model="menuItem.description" label="Popis" :name="'desc_' + section.id + '_' + idx" class="sm:col-span-2" />
									<div class="flex items-end sm:col-span-1">
										<button
											type="button"
											class="mb-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-400 ring-1 ring-inset ring-slate-200 transition hover:bg-red-50 hover:text-red-500 hover:ring-red-200"
											@click="removeItemFromSection(section.id, idx)"
										>
											<TrashIcon class="size-4" />
										</button>
									</div>
								</div>

								<!-- Allergens -->
								<div class="mt-3">
									<label class="mb-1.5 block text-sm font-medium text-slate-700">
										Alergeny
										<span v-if="menuItem.meal_id" class="text-slate-400">(z jídla)</span>
									</label>
									<div class="flex flex-wrap gap-2">
										<label
											v-for="allergen in allergens"
											:key="allergen.id"
											class="flex cursor-pointer items-center gap-2 rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset transition-all"
											:class="(menuItem.allergen_ids || []).includes(allergen.id) ? 'bg-red-50 ring-red-200 text-red-700' : 'bg-white ring-slate-200 text-slate-500 hover:ring-slate-300'"
										>
											<input
												type="checkbox"
												class="hidden"
												:checked="(menuItem.allergen_ids || []).includes(allergen.id)"
												@change="toggleAllergen(section.id, idx, allergen.id)"
											/>
											{{ allergen.number }}. {{ allergen.name }}
										</label>
									</div>
								</div>
							</div>
						</div>
					</LayoutContainer>
				</div>
			</template>

			<!-- ═══ Tab: SEO a texty ═══ -->
			<template v-if="tabs.find((t) => t.current && t.link === '#seo')">
				<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
					<div class="col-span-1 space-y-8 lg:col-span-9">
						<LayoutContainer>
							<div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
								<div class="flex items-center gap-3">
									<div class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
										<NewspaperIcon class="size-5" />
									</div>
									<LayoutTitle class="!mb-0">Obsah</LayoutTitle>
								</div>
								<div class="flex items-center gap-2">
									<span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Jazyk:</span>
									<span class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-tight text-white">
										{{ selectedLocale }}
									</span>
								</div>
							</div>

							<div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
								<BaseFormInput
									v-if="item.translations?.[selectedLocale]?.name !== undefined"
									:key="`name-${selectedLocale}`"
									v-model="item.translations[selectedLocale].name"
									label="Název"
									type="text"
									name="name"
									rules="required|min:3"
									class="col-span-full lg:col-span-1"
								/>

								<BaseFormInput
									v-if="item.translations?.[selectedLocale]?.slug !== undefined"
									:key="`slug-${selectedLocale}`"
									v-model="item.translations[selectedLocale].slug"
									label="Slug (URL)"
									type="text"
									name="slug"
									class="col-span-full lg:col-span-1"
								/>

								<BaseFormInput
									v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
									:key="`meta_title-${selectedLocale}`"
									v-model="item.translations[selectedLocale].meta_title"
									label="Meta název"
									type="text"
									name="meta_title"
									class="col-span-full lg:col-span-1"
								/>

								<div class="col-span-full rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60">
									<div class="mb-4 flex items-center gap-2">
										<GlobeAltIcon class="size-4 text-slate-400" />
										<span class="text-xs font-bold uppercase tracking-widest text-slate-500">SEO Optimalizace</span>
									</div>
									<BaseFormTextarea
										v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
										:key="`meta_description-${selectedLocale}`"
										v-model="item.translations[selectedLocale].meta_description"
										label="Meta popisek"
										name="meta_description"
										rows="2"
										class="bg-white"
									/>
								</div>

								<div class="col-span-full space-y-10 pt-4">
									<BaseFormEditor
										v-if="item.translations?.[selectedLocale]?.perex !== undefined"
										:key="`perex-${selectedLocale}`"
										v-model="item.translations[selectedLocale].perex"
										label="Perex"
										name="perex"
									/>
									<BaseFormEditor
										v-if="item.translations?.[selectedLocale]?.text !== undefined"
										:key="`text-${selectedLocale}`"
										v-model="item.translations[selectedLocale].text"
										label="Popis"
										name="text"
									/>
								</div>
							</div>
						</LayoutContainer>
					</div>

					<aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
						<LayoutActionsDetailBlock
							v-model:selected-locale="selectedLocale"
							v-model:translate-automatically="item.translateAutomatically"
							v-model:sites="item.sites"
							:allow-image="false"
							:allow-is-active="false"
							class="shadow-sm"
						/>
					</aside>
				</div>
			</template>
		</Form>
	</div>
</template>
