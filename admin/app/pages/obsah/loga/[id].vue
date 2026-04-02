<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové logo' : 'Detail loga');

const breadcrumbs = ref([
  {
    name: 'Loga',
    link: '/obsah/loga',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/loga/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  image: '' as string,
  name: '' as string,
  position: 0 as number,
  url: '' as string,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([{ field: 'url' as string, label: 'URL' as string }]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    image: string;
    name: string;
    position: number;
    url: string;
    translations: object;
  }>('/api/admin/logo/' + route.params.id, {
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
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/loga/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst logo. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/loga');
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
    type: string;
    price_type: string;
    price: number;
    tax_rate_id: number;
    currency_id: number;
    image: string;
    active: boolean;
    translations: object;
  }>(route.params.id === 'pridat' ? '/api/admin/logo' : '/api/admin/logo/' + route.params.id, {
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
            ? 'Logo bylo úspěšně vytvořeno.'
            : 'Logo bylo úspěšně upraveno.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/loga/' + response.id);
      } else if (redirect) {
        router.push('/obsah/loga');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit logo. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadItem();
});

useHead({
  title: pageTitle.value,
});

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

function updateItemImage(files) {
  item.value.image = files[0];
}

onMounted(() => {
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
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="logos"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
                >
                  <BuildingOfficeIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Identita klienta</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Verze:</span
                >
                <span
                  class="rounded-md bg-indigo-600 px-2 py-1 text-xs font-bold uppercase text-white"
                  >{{ selectedLocale }}</span
                >
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <div class="col-span-1">
                <BaseFormInput
                  v-model="item.name"
                  label="Název klienta / firmy"
                  type="text"
                  name="name"
                  rules="required|min:1"
                  placeholder="Např. Apple Inc."
                />
              </div>

              <div class="col-span-1">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.url !== undefined"
                  :key="`url-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].url"
                  label="Webová stránka (URL)"
                  type="text"
                  name="url"
                  rules="required|min:3"
                  placeholder="https://www.klient.cz"
                />
              </div>

              <div class="col-span-full pt-4">
                <div
                  class="rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 transition-colors hover:border-indigo-300"
                >
                  <div class="mb-4 flex items-center gap-2">
                    <PhotoIcon class="size-4 text-slate-400" />
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                      >Soubor loga</span
                    >
                  </div>

                  <BaseFormUploadImage
                    v-model="item.image"
                    :multiple="false"
                    type="logo"
                    format="large"
                    label="Klikněte nebo přetáhněte logo klienta"
                    @update-files="updateItemImage"
                  />

                  <p class="mt-4 text-center text-xs text-slate-400">
                    Doporučený formát: <strong>SVG nebo průhledné PNG</strong>. Minimální šířka
                    400px.
                  </p>
                </div>
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:position="item.position"
            v-model:sites="item.sites"
            :allow-position="true"
            :allow-image="false"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-slate-900 p-6 text-white shadow-xl shadow-slate-200">
            <div class="mb-3 flex items-center gap-2">
              <SparklesIcon class="size-4 text-amber-400" />
              <h4 class="text-xs font-bold uppercase tracking-widest">Tip pro zobrazení</h4>
            </div>
            <p class="text-sm leading-relaxed opacity-80">
              Loga se na webu obvykle zobrazují v řadě (carousel). Pokud má logo příliš bílého
              místa, ořízněte jej pro lepší zarovnání s ostatními.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
