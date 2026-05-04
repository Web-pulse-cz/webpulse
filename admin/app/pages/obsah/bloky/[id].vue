<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import { Form } from 'vee-validate';
import { RectangleStackIcon, GlobeAltIcon, LightBulbIcon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový blok' : 'Detail bloku');

const breadcrumbs = ref([
  { name: 'Bloky', link: '/obsah/bloky', current: false },
  {
    name: pageTitle.value,
    link: '/obsah/bloky/' + (route.params.id === 'pridat' ? 'pridat' : route.params.id),
    current: true,
  },
]);

const schemas = ref<{
  types: Array<any>;
  allowed_blockables: Array<{ key: string; class: string }>;
}>({ types: [], allowed_blockables: [] });

const item = ref({
  id: null as number | null,
  type: '' as string,
  blockable_key: 'site' as string,
  blockable_id: null as number | null,
  position: 0 as number,
  is_active: true as boolean,
  data: {} as Record<string, any>,
  translations: {} as Record<string, { data: Record<string, any> }>,
});

const currentSite = computed(() =>
  (user.value as any)?.sites?.find((s: any) => s.hash === selectedSiteHash.value),
);

const currentTypeSchema = computed(() =>
  schemas.value.types.find((t: any) => t.key === item.value.type),
);

const sharedFields = computed(() => currentTypeSchema.value?.fields ?? []);

const blockableOptions = computed(() =>
  schemas.value.allowed_blockables.map((b) => ({
    label: b.key,
    name: b.key,
    value: b.key,
  })),
);

const typeOptions = computed(() =>
  schemas.value.types.map((t: any) => ({
    label: t.label,
    name: t.label,
    value: t.key,
  })),
);

async function loadSchemas() {
  const client = useSanctumClient();
  await client('/api/admin/block/schemas', {
    method: 'GET',
    headers: { Accept: 'application/json' },
  }).then((response: any) => {
    schemas.value = response;
    if (!item.value.type && schemas.value.types.length > 0) {
      item.value.type = schemas.value.types[0].key;
    }
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/block/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      item.value = {
        id: response.id,
        type: response.type,
        blockable_key: response.blockable_key ?? 'site',
        blockable_id: response.blockable_id,
        position: response.position ?? 0,
        is_active: !!response.is_active,
        data: response.data ?? {},
        translations: response.translations ?? {},
      };
      breadcrumbs.value.pop();
      pageTitle.value = `${currentTypeSchema.value?.label ?? response.type} #${response.id}`;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/bloky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst blok.',
        severity: 'error',
      });
      router.push('/obsah/bloky');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;

  if (!item.value.blockable_id && item.value.blockable_key === 'site' && currentSite.value?.id) {
    item.value.blockable_id = currentSite.value.id;
  }

  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat' ? '/api/admin/block' : '/api/admin/block/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify({
        type: item.value.type,
        blockable_type: item.value.blockable_key,
        blockable_id: item.value.blockable_id,
        position: item.value.position,
        is_active: item.value.is_active,
        data: item.value.data,
        translations: item.value.translations,
      }),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    },
  )
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Blok byl vytvořen.' : 'Blok byl uložen.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/bloky/' + response.id);
      } else if (redirect) {
        router.push('/obsah/bloky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit blok. Zkontrolujte vyplněná pole.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function fillEmptyTranslations() {
  languageStore.languages.forEach((language: any) => {
    if (!item.value.translations[language.code]) {
      item.value.translations[language.code] = { data: {} };
    } else if (!item.value.translations[language.code].data) {
      item.value.translations[language.code].data = {};
    }
  });
}

function ensureBlockableId() {
  if (item.value.blockable_key === 'site' && currentSite.value?.id) {
    item.value.blockable_id = currentSite.value.id;
  }
}

watch(selectedSiteHash, () => {
  if (route.params.id !== 'pridat') {
    loadItem();
  } else {
    ensureBlockableId();
  }
});

watch(
  () => item.value.blockable_key,
  (newKey, oldKey) => {
    if (newKey !== oldKey) {
      item.value.blockable_id = null;
      ensureBlockableId();
    }
  },
);

useHead({
  title: pageTitle.value,
});

onMounted(async () => {
  await loadSchemas();
  fillEmptyTranslations();
  if (route.params.id !== 'pridat') {
    await loadItem();
  } else {
    ensureBlockableId();
  }
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
      slug="blocks"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <RectangleStackIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Typ a umístění</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormSelect
                v-model="item.type"
                label="Typ bloku"
                name="type"
                :options="typeOptions"
                :disabled="route.params.id !== 'pridat'"
                rules="required"
              />

              <BaseFormSelect
                v-model="item.blockable_key"
                label="Typ rodiče"
                name="blockable_key"
                :options="blockableOptions"
                rules="required"
              />

              <BaseFormInput
                v-model="item.blockable_id"
                label="ID rodiče"
                name="blockable_id"
                type="number"
                rules="required"
              />

              <BaseFormInput
                v-model="item.position"
                label="Pořadí"
                name="position"
                type="number"
              />
            </div>

            <p
              v-if="currentTypeSchema?.description"
              class="mt-4 text-xs italic text-slate-400"
            >
              {{ currentTypeSchema.description }}
            </p>
          </LayoutContainer>

          <LayoutContainer v-if="item.type">
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <GlobeAltIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Sdílená pole</LayoutTitle>
              </div>
            </div>

            <BlockFieldRenderer
              v-model="item.data"
              :fields="sharedFields"
              :translatable="false"
            />
          </LayoutContainer>

          <LayoutContainer v-if="item.type && item.translations[selectedLocale]">
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <GlobeAltIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Překlady</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                  Jazyková verze:
                </span>
                <span
                  class="inline-flex items-center rounded-md bg-slate-900 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-white"
                >
                  {{ selectedLocale }}
                </span>
              </div>
            </div>

            <BlockFieldRenderer
              v-model="item.translations[selectedLocale].data"
              :fields="sharedFields"
              :translatable="true"
              :selected-locale="selectedLocale"
            />
          </LayoutContainer>
        </div>

        <div class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:active="item.is_active"
            :allow-image="false"
            :allow-is-active="true"
            :allow-translations="true"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-4 text-amber-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-amber-900">Tip</h4>
            </div>
            <p class="text-sm leading-relaxed text-amber-800/80">
              Sdílená pole platí napříč jazyky, překládatelná se ukládají per jazyková verze.
              Pořadí ovlivní, v jakém pořadí frontend bloky vykreslí.
            </p>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
