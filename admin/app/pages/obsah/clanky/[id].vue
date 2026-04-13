<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';
import { BookmarkSquareIcon, CalendarDaysIcon, GlobeAltIcon, PencilSquareIcon, FolderIcon, DocumentTextIcon, ArrowDownTrayIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje a zařazení', link: '#info', current: false },
  { name: 'Obsah článku', link: '#obsah', current: false },
  { name: 'Soubory', link: '#soubory', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový článek' : 'Detail článku');

const breadcrumbs = ref([
  {
    name: 'Blogové články',
    link: '/obsah/clanky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/clanky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  status: 'draft' as string,
  published_from: null as string | null,
  published_to: null as string | null,
  image: '' as string,
  translations: {} as object,
  categories: [] as number[],
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

const postFiles = ref([] as any[]);
const categories = ref([]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    status: string;
    published_from: string | null;
    published_to: string | null;
    image: string;
    translations: object;
    categories: number[];
  }>('/api/admin/post/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = response.sites.map((site) => site.id);
      postFiles.value = response.files || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/clanky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
      item.value.published_to = item.value.published_to
        ? new Date(item.value.published_to).toISOString().slice(0, 16)
        : null;
      item.value.published_from = item.value.published_from
        ? new Date(item.value.published_from).toISOString().slice(0, 16)
        : null;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst článek. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/clanky');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/post/category', {
    method: 'GET',
    query: {
      orderBy: 'position',
      orderWay: 'asc',
    },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      categories.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorie. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    status: string;
    published_from: string | null;
    published_to: string | null;
    image: string;
    translations: object;
    categories: number[];
  }>(route.params.id === 'pridat' ? '/api/admin/post' : '/api/admin/post/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Článek byl úspěšně vytvořen.'
            : 'Článek byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/clanky/' + response.id);
      } else if (redirect) {
        router.push('/obsah/clanky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit článek. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function uploadPostFile(event: Event) {
	const target = event.target as HTMLInputElement;
	const file = target.files?.[0];
	if (!file || !item.value.id) return;

	const client = useSanctumClient();
	const formData = new FormData();
	formData.append('file', file);

	loading.value = true;
	await client('/api/admin/post/' + item.value.id + '/file', {
		method: 'POST',
		body: formData,
		headers: { 'X-Site-Hash': selectedSiteHash.value },
	})
		.then((r) => {
			$toast.show({ summary: 'Hotovo', detail: 'Soubor nahrán.', severity: 'success' });
			postFiles.value = r.files || [];
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se nahrát soubor.', severity: 'error' });
		})
		.finally(() => {
			loading.value = false;
			target.value = '';
		});
}

async function downloadPostFile(file: any) {
	const client = useSanctumClient();
	try {
		const res = await client.raw('/api/admin/post/' + item.value.id + '/file/' + file.id, {
			method: 'GET',
			credentials: 'include',
			responseType: 'blob',
		});
		if (!res.ok) throw new Error('Chyba');
		const blob = res._data as Blob;
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = file.name || 'soubor-' + file.id;
		document.body.appendChild(a);
		a.click();
		a.remove();
		URL.revokeObjectURL(url);
	} catch (e) {
		$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
	}
}

async function deletePostFile(file: any) {
	const client = useSanctumClient();
	await client('/api/admin/post/' + item.value.id + '/file/' + file.id, {
		method: 'DELETE',
		headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
	})
		.then(() => {
			postFiles.value = postFiles.value.filter((f: any) => f.id !== file.id);
			$toast.show({ summary: 'Hotovo', detail: 'Soubor smazán.', severity: 'success' });
		})
		.catch(() => {
			$toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat soubor.', severity: 'error' });
		});
}

watch(selectedSiteHash, () => {
  loadItem();
  loadCategories();
});

useHead({
  title: pageTitle.value,
});

function addRemoveItemCategory(categoryId) {
  if (item.value.categories.includes(categoryId)) {
    item.value.categories = item.value.categories.filter((category) => category !== categoryId);
    return;
  } else {
    item.value.categories.push(categoryId);
  }
}

function fillEmptyTranslations() {
  // Set default translations for all languages
  languageStore.languages.forEach((language) => {
    if (item.value.translations[language.code] === undefined) {
      item.value.translations[language.code] = {};
      translatableAttributes.value.forEach((attribute) => {
        if (item.value.translations[language.code][attribute.field] === undefined) {
          item.value.translations[language.code][attribute.field] = '';
        }
      });
    }
  });
}

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

onMounted(() => {
  loadCategories();
  if (route.params.id !== 'pridat') {
    loadItem();
  }
  fillEmptyTranslations();
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="posts"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-8 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <CalendarDaysIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Plánování publikace</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.published_from"
                  label="Publikovat od"
                  type="datetime-local"
                  name="published_from"
                />
                <BaseFormInput
                  v-model="item.published_to"
                  label="Publikovat do (volitelné)"
                  type="datetime-local"
                  name="published_to"
                />
              </div>
            </LayoutContainer>

            <LayoutContainer v-if="categories.length > 0">
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                >
                  <BookmarkSquareIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Zařazení do kategorií</LayoutTitle>
              </div>

              <div class="rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  <div
                    v-for="(category, key) in categories"
                    :key="key"
                    class="flex items-center rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 transition-all hover:ring-indigo-300"
                  >
                    <BaseFormCheckbox
                      :label="category.name"
                      :name="String(category.id)"
                      :value="item.categories.includes(category.id)"
                      :checked="item.categories.includes(category.id)"
                      class="w-full flex-row-reverse justify-between font-medium text-slate-700"
                      @change="addRemoveItemCategory(category.id)"
                    />
                  </div>
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:image="item.image"
              v-model:state="item.status"
              v-model:sites="item.sites"
              :allow-translations="false"
              :allow-state="true"
              :states="[
                { value: 'draft', name: 'Koncept' },
                { value: 'published', name: 'Publikováno' },
                { value: 'archived', name: 'Archivováno' },
              ]"
              image-type="post"
            />
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#obsah')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
                <div class="flex items-center gap-3">
                  <div
                    class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                  >
                    <PencilSquareIcon class="size-5" />
                  </div>
                  <LayoutTitle class="!mb-0">Editor obsahu</LayoutTitle>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                    >Jazyk:</span
                  >
                  <span
                    class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-tight text-white"
                  >
                    {{ selectedLocale }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.name !== undefined"
                  :key="`name-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].name"
                  label="Hlavní nadpis (H1)"
                  type="text"
                  name="name"
                  rules="required|min:3"
                  class="col-span-full lg:col-span-1"
                />

                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                  :key="`meta_title-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_title"
                  label="Meta název (pro Google)"
                  type="text"
                  name="meta_title"
                  class="col-span-full lg:col-span-1"
                />

                <div
                  class="col-span-full rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200"
                >
                  <div class="mb-4 flex items-center gap-2">
                    <GlobeAltIcon class="size-4 text-slate-400" />
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                      >SEO Popisek</span
                    >
                  </div>
                  <BaseFormTextarea
                    v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                    :key="`meta_description-${selectedLocale}`"
                    v-model="item.translations[selectedLocale].meta_description"
                    label="Meta popis (Snippet ve výsledcích vyhledávání)"
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
                    label="Perex (Stručný úvod článku)"
                    name="perex"
                  />
                  <BaseFormEditor
                    v-if="item.translations?.[selectedLocale]?.text !== undefined"
                    :key="`text-${selectedLocale}`"
                    v-model="item.translations[selectedLocale].text"
                    label="Hlavní text příspěvku"
                    name="text"
                  />
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:selected-locale="selectedLocale"
              v-model:translate-automatically="item.translateAutomatically"
              :allow-image="false"
              :allow-sites="false"
              class="shadow-sm"
            />

            <div class="mt-6 rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
              <h4 class="text-xs font-bold uppercase tracking-widest text-amber-900">
                Publikační tip
              </h4>
              <p class="mt-2 text-sm leading-relaxed text-amber-800/80">
                Perex by měl být krátký a úderný. Zobrazuje se v náhledech na hlavní stránce a ve
                výpisu blogu.
              </p>
            </div>
          </div>
        </div>
      </template>

      <!-- Soubory tab -->
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#soubory')">
        <LayoutContainer>
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <FolderIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Soubory</LayoutTitle>
            </div>
            <label
              v-if="item.id"
              class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600"
            >
              <ArrowDownTrayIcon class="size-5 rotate-180" />
              Nahrát soubor
              <input type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" @change="uploadPostFile" />
            </label>
          </div>

          <div v-if="!item.id" class="py-8 text-center text-sm text-slate-400">
            Pro nahrání souborů nejprve uložte článek.
          </div>
          <div v-else-if="!postFiles.length" class="py-8 text-center text-sm text-slate-400">
            Žádné soubory.
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="file in postFiles"
              :key="file.id"
              class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="flex items-center gap-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                  <DocumentTextIcon class="size-5" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">{{ file.name }}</p>
                  <p class="text-xs text-slate-400">
                    {{ file.mime_type }}
                    <span v-if="file.size" class="ml-2">{{ (file.size / 1024).toFixed(0) }} KB</span>
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                  @click="downloadPostFile(file)"
                >
                  Stáhnout
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
                  @click="deletePostFile(file)"
                >
                  <TrashIcon class="size-4" />
                </button>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
