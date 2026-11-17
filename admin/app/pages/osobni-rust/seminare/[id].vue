<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  AcademicCapIcon,
  ClipboardDocumentCheckIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline';
import type { PersonalGrowthTask } from '~/components/PersonalGrowth/TaskList.vue';

const { $toast } = useNuxtApp();
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const isNew = route.params.id === 'pridat';
const listLink = '/osobni-rust/seminare';

const typeOptions = [
  { value: 'seminar', name: 'Seminář' },
  { value: 'training', name: 'Školení' },
  { value: 'leadership', name: 'Leadership' },
];

const pageTitle = ref(isNew ? 'Nový seminář' : 'Detail semináře');
const breadcrumbs = ref([
  { name: 'Semináře', link: listLink, current: false },
  { name: pageTitle.value, link: route.path, current: true },
]);

const item = ref({
  id: null as number | null,
  place: '' as string,
  date: '' as string,
  organizer: '' as string,
  type: 'seminar' as string,
  notes: '' as string,
  tasks: [] as PersonalGrowthTask[],
});

const { markClean } = useDirtyGuard(item);

function applyResponse(response: any) {
  item.value = {
    id: response.id,
    place: response.place ?? '',
    date: response.date ?? '',
    organizer: response.organizer ?? '',
    type: response.type ?? 'seminar',
    notes: response.notes ?? '',
    tasks: response.tasks ?? [],
  };
  pageTitle.value =
    [response.type_label, item.value.organizer || item.value.place].filter(Boolean).join(' – ') ||
    'Detail semináře';
  breadcrumbs.value[1] = { name: pageTitle.value, link: route.path, current: true };
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/personal-growth/seminar/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json' },
  })
    .then((response) => applyResponse(response))
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst seminář.',
        severity: 'error',
      });
      router.push(listLink);
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

  await client<{ id: number }>(
    isNew
      ? '/api/admin/personal-growth/seminar'
      : '/api/admin/personal-growth/seminar/' + route.params.id,
    {
      method: 'POST',
      body: item.value,
      headers: { Accept: 'application/json' },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail: isNew ? 'Seminář byl přidán.' : 'Seminář byl uložen.',
        severity: 'success',
      });
      applyResponse(response);
      markClean();
      if (redirect) {
        router.push(listLink);
      } else if (isNew) {
        router.push(`${listLink}/${response.id}`);
      }
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit seminář. Zkontrolujte vyplněná pole.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({ title: pageTitle });

onMounted(() => {
  if (isNew) {
    markClean();
  } else {
    loadItem();
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
      :actions="[{ type: 'save' }, { type: 'save-and-leave' }]"
      slug="seminars"
      :saving="loading"
      @save="saveItem"
    />

    <div
      v-if="loading && !item.id && !isNew"
      class="grid animate-pulse grid-cols-1 gap-8 lg:grid-cols-12"
      aria-busy="true"
    >
      <div
        class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-12"
      >
        <div class="h-5 w-1/3 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-40 rounded bg-slate-100"></div>
      </div>
    </div>

    <Form v-else ref="formRef" @submit="() => saveItem(false)">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-12">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <AcademicCapIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Seminář</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormSelect
                v-model="item.type"
                label="Typ semináře"
                name="type"
                rules="required"
                :options="typeOptions"
              />
              <BaseFormInput v-model="item.date" label="Datum" name="date" type="date" />
              <BaseFormInput
                v-model="item.place"
                label="Místo"
                name="place"
                placeholder="Např. Praha, Hotel Hilton"
              />
              <BaseFormInput
                v-model="item.organizer"
                label="Organizátor"
                name="organizer"
                placeholder="Kdo seminář pořádal"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <PencilSquareIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Poznámky</LayoutTitle>
            </div>
            <BaseFormEditor v-model="item.notes" label="Poznámky" name="notes" />
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <ClipboardDocumentCheckIcon class="size-5" />
              </div>
              <div>
                <LayoutTitle class="!mb-0">Úkoly</LayoutTitle>
                <p class="text-xs text-slate-500">Co chci udělat na základě semináře.</p>
              </div>
            </div>
            <PersonalGrowthTaskList v-model="item.tasks" />
          </LayoutContainer>
        </div>
      </div>
    </Form>
  </div>
</template>
