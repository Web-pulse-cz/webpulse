<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const languageStore = useLanguageStore();
const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);
const locale = ref('cs');

const tabs = ref([
	{ name: 'Jídelní lístek', link: '#listek', current: false },
	{ name: 'SEO a texty', link: '#seo', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový jídelní lístek' : 'Detail jídelního lístku');
const breadcrumbs = ref([
	{ name: 'Menu', link: '/restaurace/menu', current: false },
	{ name: pageTitle.value, link: '/restaurace/menu/pridat', current: true },
]);

const sections = ref([]); // all available menu sections
const meals = ref([]); // all available meals
const allergens = ref([]); // all allergens

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
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		item.value = r;
		if (!item.value.items) item.value.items = [];
		pageTitle.value = r.name || 'Jídelní lístek';
		breadcrumbs.value[1] = { name: pageTitle.value, link: '/restaurace/menu/' + route.params.id, current: true };
	}).catch(() => { error.value = true; })
	.finally(() => { loading.value = false; });
}

async function loadSections() {
	const client = useSanctumClient();
	await client('/api/admin/food/menu-section', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => { sections.value = r; }).catch(() => {});
}

async function loadMeals() {
	const client = useSanctumClient();
	await client('/api/admin/food/meal', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		const d = r?.data || r;
		meals.value = d;
	}).catch(() => {});
}

async function loadAllergens() {
	const client = useSanctumClient();
	await client('/api/admin/food/allergen', {
		method: 'GET', headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		const d = r?.data || r;
		allergens.value = d;
	}).catch(() => {});
}

// ─── Save ──────────────────────────────────────────────────

async function saveItem(redirect = true) {
	const client = useSanctumClient();
	loading.value = true;
	await client(route.params.id === 'pridat' ? '/api/admin/food/menu' : '/api/admin/food/menu/' + route.params.id, {
		method: 'POST', body: JSON.stringify(item.value),
		headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
	}).then((r) => {
		$toast.show({ summary: 'Hotovo', detail: 'Jídelní lístek uložen.', severity: 'success' });
		if (!redirect && route.params.id === 'pridat') router.push('/restaurace/menu/' + r.id);
		else if (redirect) router.push('/restaurace/menu');
		else loadItem();
	}).catch(() => { $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit.', severity: 'error' }); })
	.finally(() => { loading.value = false; });
}

// ─── Menu Items ────────────────────────────────────────────

function addItem() {
	item.value.items.push({
		section_id: null,
		meal_id: null,
		name: '',
		description: '',
		price: 0,
		weight: '',
		allergen_ids: [],
		position: item.value.items.length,
	});
}

function removeItem(index: number) {
	item.value.items.splice(index, 1);
}

function onMealSelected(index: number) {
	const menuItem = item.value.items[index];
	if (!menuItem.meal_id) return;

	const meal = meals.value.find((m: any) => m.id === menuItem.meal_id);
	if (meal) {
		menuItem.name = meal.name || '';
		menuItem.price = meal.price || 0;
		menuItem.weight = meal.weight || '';
		// Auto-fill allergens from meal
		if (meal.allergens?.length) {
			menuItem.allergen_ids = meal.allergens.map((a: any) => a.id);
		}
	}
}

function toggleAllergen(itemIndex: number, allergenId: number) {
	const ids = item.value.items[itemIndex].allergen_ids || [];
	if (ids.includes(allergenId)) {
		item.value.items[itemIndex].allergen_ids = ids.filter((id: number) => id !== allergenId);
	} else {
		item.value.items[itemIndex].allergen_ids = [...ids, allergenId];
	}
}

function downloadPdf() {
	window.open('/api/admin/food/menu/' + route.params.id + '/pdf', '_blank');
}

// Items grouped by section for display
const itemsBySection = computed(() => {
	const groups: Record<string, any[]> = {};
	for (const it of item.value.items) {
		const sectionName = sections.value.find((s: any) => s.id === it.section_id)?.name || 'Bez sekce';
		if (!groups[sectionName]) groups[sectionName] = [];
		groups[sectionName].push(it);
	}
	return groups;
});

// ─── Lifecycle ─────────────────────────────────────────────

watchEffect(() => {
	const h = route.hash;
	if (h) tabs.value.forEach((t) => { t.current = t.link === h; });
	else { tabs.value[0].current = true; router.push(route.path + '#listek'); }
});

useHead({ title: pageTitle.value });
function initTranslations() {
	for (const lang of languageStore.languages) {
		if (!item.value.translations[lang.code]) {
			item.value.translations[lang.code] = {
				name: '', slug: '', perex: '', text: '', meta_title: '', meta_description: '',
			};
		}
	}
}

onMounted(() => {
	loadSections(); loadMeals(); loadAllergens();
	if (route.params.id !== 'pridat') {
		loadItem().then(() => initTranslations());
	} else {
		initTranslations();
	}
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" :actions="[{ type: 'save' }, { type: 'save-and-stay' }]" :modify-bottom="false" slug="menus" @save="saveItem" />
		<LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

		<Form @submit="saveItem">
			<!-- ═══ Jídelní lístek ═══ -->
			<template v-if="tabs.find((t) => t.current && t.link === '#listek')">
				<div class="space-y-6">
					<!-- Actions -->
					<div class="flex items-center justify-between">
						<div class="flex gap-2">
							<button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500" @click="addItem">Přidat položku</button>
							<button v-if="route.params.id !== 'pridat'" type="button" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600" @click="downloadPdf">Export PDF</button>
						</div>
					</div>

					<!-- Items list -->
					<div v-if="!item.items?.length" class="py-16 text-center text-sm text-slate-400">
						Jídelní lístek je prázdný. Přidejte první položku.
					</div>

					<div v-else class="space-y-3">
						<div
							v-for="(menuItem, index) in item.items"
							:key="index"
							class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
						>
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
								<!-- Section -->
								<BaseFormSelect
									v-model="menuItem.section_id"
									label="Sekce"
									name="item_section"
									:options="sections.map((s) => ({ value: s.id, name: s.name }))"
									class="sm:col-span-2"
								/>

								<!-- Meal (existing or empty for custom) -->
								<div class="sm:col-span-3">
									<label class="mb-1 block text-xs font-medium text-slate-700">Z existujícího jídla</label>
									<select
										v-model="menuItem.meal_id"
										class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
										@change="onMealSelected(index)"
									>
										<option :value="null">— Vlastní položka —</option>
										<option v-for="meal in meals" :key="meal.id" :value="meal.id">{{ meal.name }}</option>
									</select>
								</div>

								<!-- Name -->
								<BaseFormInput v-model="menuItem.name" label="Název" name="item_name" class="sm:col-span-3" />

								<!-- Price -->
								<BaseFormInput v-model="menuItem.price" label="Cena (Kč)" type="number" name="item_price" :step="1" class="sm:col-span-1" />

								<!-- Weight -->
								<BaseFormInput v-model="menuItem.weight" label="Gramáž" name="item_weight" placeholder="200g" class="sm:col-span-1" />

								<!-- Delete -->
								<div class="flex items-end sm:col-span-2">
									<button type="button" class="rounded-lg p-2 text-red-500 hover:bg-red-50" @click="removeItem(index)">
										<TrashIcon class="size-5" />
									</button>
								</div>
							</div>

							<!-- Description -->
							<div class="mt-3">
								<BaseFormInput v-model="menuItem.description" label="Popis" name="item_desc" placeholder="Krátký popis jídla..." />
							</div>

							<!-- Allergens -->
							<div class="mt-3">
								<label class="mb-1 block text-xs font-medium text-slate-700">
									Alergeny
									<span v-if="menuItem.meal_id" class="text-slate-400">(předvyplněno z jídla)</span>
								</label>
								<div class="flex flex-wrap gap-2">
									<label
										v-for="allergen in allergens"
										:key="allergen.id"
										class="cursor-pointer rounded-full px-2.5 py-1 text-[11px] font-medium transition"
										:class="(menuItem.allergen_ids || []).includes(allergen.id) ? 'bg-red-100 text-red-700 ring-1 ring-red-300' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
									>
										<input type="checkbox" class="hidden" :checked="(menuItem.allergen_ids || []).includes(allergen.id)" @change="toggleAllergen(index, allergen.id)" />
										{{ allergen.number }}. {{ allergen.name }}
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>

			<!-- ═══ SEO a texty ═══ -->
			<template v-if="tabs.find((t) => t.current && t.link === '#seo')">
				<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
					<div class="col-span-1 space-y-8 lg:col-span-9">
						<LayoutContainer>
							<LayoutTitle>Překlady</LayoutTitle>
							<div class="mb-4 flex gap-2">
								<button
									v-for="lang in languageStore.languages"
									:key="lang.code"
									type="button"
									class="rounded-lg px-3 py-1 text-xs font-medium transition"
									:class="locale === lang.code ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
									@click="locale = lang.code"
								>
									{{ lang.name }}
								</button>
							</div>
							<div v-for="lang in languageStore.languages" :key="lang.code" v-show="locale === lang.code" class="space-y-4">
								<template v-if="item.translations[lang.code]">
									<BaseFormInput v-model="item.translations[lang.code].name" label="Název" :name="'name_' + lang.code" />
									<BaseFormInput v-model="item.translations[lang.code].slug" label="Slug" :name="'slug_' + lang.code" />
									<BaseFormTextarea v-model="item.translations[lang.code].perex" label="Perex" :name="'perex_' + lang.code" rows="3" />
									<BaseFormTextarea v-model="item.translations[lang.code].text" label="Text" :name="'text_' + lang.code" rows="5" />
									<BaseFormInput v-model="item.translations[lang.code].meta_title" label="Meta title" :name="'meta_title_' + lang.code" />
									<BaseFormTextarea v-model="item.translations[lang.code].meta_description" label="Meta description" :name="'meta_desc_' + lang.code" rows="2" />
								</template>
							</div>
						</LayoutContainer>
					</div>
					<div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
						<LayoutActionsDetailBlock v-model:sites="item.sites" />
					</div>
				</div>
			</template>
		</Form>
	</div>
</template>
