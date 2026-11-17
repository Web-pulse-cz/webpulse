<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  BookOpenIcon,
  CheckCircleIcon,
  ClipboardDocumentCheckIcon,
  PencilSquareIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline';
import type { PersonalGrowthTask } from '~/components/PersonalGrowth/TaskList.vue';

const { $toast } = useNuxtApp();
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const isNew = route.params.id === 'pridat';
const listLink = '/osobni-rust/knihy';

const pageTitle = ref(isNew ? 'Nová kniha' : 'Detail knihy');
const breadcrumbs = ref([
  { name: 'Knihy', link: listLink, current: false },
  { name: pageTitle.value, link: route.path, current: true },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  author: '' as string,
  notes: '' as string,
  keywords: [] as string[],
  is_read: false as boolean,
  image: '' as string,
  tasks: [] as PersonalGrowthTask[],
});

const { markClean } = useDirtyGuard(item);

function applyResponse(response: any) {
  item.value = {
    id: response.id,
    name: response.name ?? '',
    author: response.author ?? '',
    notes: response.notes ?? '',
    keywords: response.keywords ?? [],
    is_read: !!response.is_read,
    image: response.image ?? '',
    tasks: response.tasks ?? [],
  };
  pageTitle.value = item.value.name;
  breadcrumbs.value[1] = { name: pageTitle.value, link: route.path, current: true };
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/personal-growth/book/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json' },
  })
    .then((response) => applyResponse(response))
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst knihu.',
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
      ? '/api/admin/personal-growth/book'
      : '/api/admin/personal-growth/book/' + route.params.id,
    {
      method: 'POST',
      body: item.value,
      headers: { Accept: 'application/json' },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail: isNew ? 'Kniha byla přidána.' : 'Kniha byla uložena.',
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
        detail: 'Nepodařilo se uložit knihu. Zkontrolujte vyplněná pole.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function updateImage(files: string[]) {
  item.value.image = files[0] ?? '';
}

function removeImage() {
  item.value.image = '';
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
      slug="books"
      :saving="loading"
      @save="saveItem"
    />

    <div
      v-if="loading && !item.id && !isNew"
      class="grid animate-pulse grid-cols-1 gap-8 lg:grid-cols-12"
      aria-busy="true"
    >
      <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-8">
        <div class="h-5 w-1/3 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-40 rounded bg-slate-100"></div>
      </div>
      <div class="h-80 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 lg:col-span-4"></div>
    </div>

    <Form v-else ref="formRef" @submit="() => saveItem(false)">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-8">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <BookOpenIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Kniha</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormInput
                v-model="item.name"
                label="Název"
                name="name"
                rules="required"
                placeholder="Např. Atomové návyky"
              />
              <BaseFormInput
                v-model="item.author"
                label="Autor"
                name="author"
                placeholder="Např. James Clear"
              />
              <div class="lg:col-span-2">
                <BaseFormTagInput
                  v-model="item.keywords"
                  label="Klíčová slova"
                  name="keywords"
                  placeholder="Např. návyky, produktivita…"
                />
              </div>
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
                <p class="text-xs text-slate-500">Co chci udělat na základě knihy.</p>
              </div>
            </div>
            <PersonalGrowthTaskList v-model="item.tasks" />
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-4">
          <LayoutContainer>
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <CheckCircleIcon
                  class="size-5"
                  :class="item.is_read ? 'text-emerald-500' : 'text-slate-300'"
                />
                <span class="text-sm font-medium text-slate-700">
                  {{ item.is_read ? 'Přečteno' : 'Nepřečteno' }}
                </span>
              </div>
              <BaseFormSwitch v-model:enabled="item.is_read" label="Přečteno" />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-4 flex items-center gap-2">
              <PhotoIcon class="size-4 text-slate-400" />
              <span class="label-caps text-slate-500">Obálka knihy</span>
            </div>
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="false"
              type="book"
              format="medium"
              label="Klikněte nebo přetáhněte obálku"
              @update-files="updateImage"
              @remove-file="removeImage"
            />
          </LayoutContainer>
        </aside>
      </div>
    </Form>
  </div>
</template>
