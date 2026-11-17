<script setup lang="ts">
import { inject, ref, computed, watch } from 'vue';
import { Form } from 'vee-validate';
import {
  MapPinIcon,
  Squares2X2Icon,
  FlagIcon,
  PlusIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const isNew = computed(() => route.params.id === 'pridat');

const pageTitle = ref(isNew.value ? 'Nové hřiště' : 'Detail hřiště');

const breadcrumbs = ref([
  { name: 'Hřiště', link: '/discgolf/hriste', current: false },
  { name: pageTitle.value, link: '/discgolf/hriste/pridat', current: true },
]);

interface Hole {
  id?: number | null;
  number: number;
  par: number;
  meters: number | null;
}

interface Layout {
  id?: number | null;
  name: string;
  total_meters: number | null;
  holes: Hole[];
}

const item = ref({
  id: null as number | null,
  name: '' as string,
  address: '' as string,
  phone: '' as string,
  position: 0 as number,
  sites: [] as number[],
  layouts: [] as Layout[],
});

const { markClean } = useDirtyGuard(item);

function newHole(number: number): Hole {
  return { id: null, number, par: 3, meters: null };
}

function newLayout(): Layout {
  return {
    id: null,
    name: '',
    total_meters: null,
    holes: [newHole(1)],
  };
}

function addLayout() {
  item.value.layouts.push(newLayout());
}

function removeLayout(index: number) {
  item.value.layouts.splice(index, 1);
}

function renumberHoles(layout: Layout) {
  layout.holes.forEach((hole, index) => {
    hole.number = index + 1;
  });
}

function addHole(layout: Layout) {
  layout.holes.push(newHole(layout.holes.length + 1));
  renumberHoles(layout);
}

function removeHole(layout: Layout, holeIndex: number) {
  layout.holes.splice(holeIndex, 1);
  renumberHoles(layout);
}

const layoutPar = computed(() => (layout: Layout) => {
  return layout.holes.reduce((sum, hole) => sum + (Number(hole.par) || 0), 0);
});

const layoutMeters = computed(() => (layout: Layout) => {
  const fromHoles = layout.holes.reduce((sum, hole) => sum + (Number(hole.meters) || 0), 0);
  if (fromHoles > 0) return fromHoles;
  return Number(layout.total_meters) || 0;
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/discgolf/course/' + route.params.id, {
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
        name: response.name || '',
        address: response.address || '',
        phone: response.phone || '',
        position: response.position || 0,
        sites: response.sites?.map((s: any) => s.id) || [],
        layouts: (response.layouts || []).map((layout: any) => ({
          id: layout.id,
          name: layout.name || '',
          total_meters: layout.total_meters ?? null,
          holes: (layout.holes || []).map((hole: any) => ({
            id: hole.id,
            number: hole.number,
            par: hole.par,
            meters: hole.meters ?? null,
          })),
        })),
      };
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name || 'Detail hřiště';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/discgolf/hriste/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst hřiště.',
        severity: 'error',
      });
      router.push('/discgolf/hriste');
    })
    .finally(() => {
      loading.value = false;
      markClean();
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;

  await client(
    isNew.value ? '/api/admin/discgolf/course' : '/api/admin/discgolf/course/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    },
  )
    .then((response: any) => {
      markClean();
      $toast.show({
        summary: 'Hotovo',
        detail: isNew.value ? 'Hřiště bylo vytvořeno.' : 'Hřiště bylo upraveno.',
        severity: 'success',
      });
      if (!redirect && isNew.value) router.push('/discgolf/hriste/' + response.id);
      else if (redirect) router.push('/discgolf/hriste');
      else loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit hřiště.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({
  title: pageTitle,
});
watch(selectedSiteHash, () => {
  if (!isNew.value) loadItem();
});

onMounted(() => {
  if (!isNew.value) {
    loadItem();
  } else {
    item.value.layouts = [newLayout()];
  }
  if (isNew.value) markClean();
});

definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-leave' }]"
      slug="courses"
      :saving="loading"
      @save="saveItem"
    />
    <div
      v-if="loading && !item.id && !isNew"
      class="grid animate-pulse grid-cols-1 gap-8 lg:grid-cols-12"
      aria-busy="true"
    >
      <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-9">
        <div class="h-5 w-1/3 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-24 rounded bg-slate-100"></div>
      </div>
      <div class="h-64 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 lg:col-span-3"></div>
    </div>
    <Form v-else ref="formRef" @submit="() => saveItem(false)">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <MapPinIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.name"
                label="Název hřiště"
                type="text"
                name="name"
                rules="required|min:2"
                class="sm:col-span-2"
              />
              <BaseFormInput v-model="item.address" label="Adresa" type="text" name="address" />
              <BaseFormInput v-model="item.phone" label="Telefon" type="text" name="phone" />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <Squares2X2Icon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Layouty a jamky</LayoutTitle>
              </div>
              <button
                type="button"
                class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                @click="addLayout"
              >
                <PlusIcon class="size-4" />
                Přidat layout
              </button>
            </div>

            <div
              v-if="item.layouts.length === 0"
              class="rounded-2xl border-2 border-dashed border-slate-200 py-10 text-center text-sm italic text-slate-400"
            >
              Zatím žádné layouty. Přidejte první layout.
            </div>

            <div class="space-y-6">
              <div
                v-for="(layout, layoutIndex) in item.layouts"
                :key="layoutIndex"
                class="overflow-hidden rounded-2xl ring-1 ring-slate-200"
              >
                <div class="flex flex-wrap items-end gap-4 bg-slate-50 px-5 py-4">
                  <div class="min-w-[200px] flex-1">
                    <BaseFormInput
                      v-model="layout.name"
                      label="Název layoutu"
                      type="text"
                      :name="`layout_name_${layoutIndex}`"
                      rules="required|min:1"
                      placeholder="Např. Krátký okruh"
                    />
                  </div>
                  <div class="w-40">
                    <BaseFormInput
                      v-model="layout.total_meters"
                      label="Celkem metrů (volitelné)"
                      type="number"
                      :name="`layout_total_meters_${layoutIndex}`"
                    />
                  </div>
                  <div class="flex items-center gap-4 pb-2">
                    <div class="text-center">
                      <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        Par
                      </div>
                      <div class="text-lg font-extrabold tabular-nums text-slate-900">
                        {{ layoutPar(layout) }}
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        Jamek
                      </div>
                      <div class="text-lg font-extrabold tabular-nums text-slate-900">
                        {{ layout.holes.length }}
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        Metrů
                      </div>
                      <div class="text-lg font-extrabold tabular-nums text-slate-900">
                        {{ layoutMeters(layout) }}
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="ml-auto flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition-colors hover:bg-rose-50"
                    @click="removeLayout(layoutIndex)"
                  >
                    <TrashIcon class="size-4" />
                    Odebrat layout
                  </button>
                </div>

                <div class="p-5">
                  <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                    <table class="w-full text-left text-sm">
                      <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                          <th class="w-20 px-4 py-3">Jamka</th>
                          <th class="px-4 py-3">Par</th>
                          <th class="px-4 py-3">Metrů</th>
                          <th class="w-16 px-4 py-3"></th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="(hole, holeIndex) in layout.holes" :key="holeIndex">
                          <td class="px-4 py-2">
                            <span
                              class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-600"
                            >
                              <FlagIcon class="mr-0.5 size-3.5" />{{ hole.number }}
                            </span>
                          </td>
                          <td class="px-4 py-2">
                            <input
                              v-model.number="hole.par"
                              type="number"
                              min="1"
                              class="w-24 rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                          </td>
                          <td class="px-4 py-2">
                            <input
                              v-model.number="hole.meters"
                              type="number"
                              min="0"
                              class="w-28 rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                          </td>
                          <td class="px-4 py-2 text-right">
                            <button
                              type="button"
                              class="rounded-lg p-2 text-rose-600 transition-colors hover:bg-rose-50"
                              :disabled="layout.holes.length === 1"
                              :class="
                                layout.holes.length === 1 ? 'cursor-not-allowed opacity-40' : ''
                              "
                              @click="removeHole(layout, holeIndex)"
                            >
                              <TrashIcon class="size-4" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <button
                    type="button"
                    class="mt-3 flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:border-indigo-400 hover:text-indigo-600"
                    @click="addHole(layout)"
                  >
                    <PlusIcon class="size-4" />
                    Přidat jamku
                  </button>
                </div>
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:position="item.position"
            v-model:sites="item.sites"
            :allow-translations="false"
            :allow-image="false"
            :allow-is-active="false"
            :allow-position="true"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
