<script setup lang="ts">
import { inject, ref, computed, watch } from 'vue';
import { Form } from 'vee-validate';
import { UserGroupIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const isNew = computed(() => route.params.id === 'pridat');

const pageTitle = ref(isNew.value ? 'Nový hráč' : 'Detail hráče');

const breadcrumbs = ref([
  { name: 'Hráči', link: '/discgolf/hraci', current: false },
  { name: pageTitle.value, link: '/discgolf/hraci/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  include_in_stats: true as boolean,
  position: 0 as number,
  sites: [] as number[],
});

const { markClean } = useDirtyGuard(item);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/discgolf/player/' + route.params.id, {
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
        include_in_stats: response.include_in_stats ?? true,
        position: response.position || 0,
        sites: response.sites?.map((s: any) => s.id) || [],
      };
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name || 'Detail hráče';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/discgolf/hraci/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst hráče.',
        severity: 'error',
      });
      router.push('/discgolf/hraci');
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
    isNew.value ? '/api/admin/discgolf/player' : '/api/admin/discgolf/player/' + route.params.id,
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
        detail: isNew.value ? 'Hráč byl vytvořen.' : 'Hráč byl upraven.',
        severity: 'success',
      });
      if (!redirect && isNew.value) router.push('/discgolf/hraci/' + response.id);
      else if (redirect) router.push('/discgolf/hraci');
      else loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit hráče.',
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
  if (!isNew.value) loadItem();
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
      slug="players"
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
                <UserGroupIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Údaje hráče</LayoutTitle>
            </div>

            <div class="max-w-xl space-y-6">
              <BaseFormInput
                v-model="item.name"
                label="Jméno hráče"
                type="text"
                name="name"
                rules="required|min:2"
                placeholder="Např. Jan Novák"
              />

              <div
                class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 ring-1 ring-slate-200"
              >
                <div>
                  <div class="font-semibold text-slate-800">Zahrnout do statistik</div>
                  <div class="text-xs text-slate-500">
                    Vypni pro jednorázové/hostující hráče — zůstanou v záznamech her, ale nebudou ve
                    statistikách hráčů ani hřišť.
                  </div>
                </div>
                <BaseFormSwitch v-model:enabled="item.include_in_stats" />
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
