<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová fáze procesu' : 'Detail fáze procesu');

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Fáze procesu',
    link: '/kontakty/faze',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/kontakty/faze/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string,
  position: 0 as number,
  show_in_statistics: true as boolean,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string;
    position: number;
    show_in_statistics: boolean;
  }>('/api/admin/contact/phase/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/faze/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fázi procesu. Zkuste to prosím později.',
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
    color: string;
    position: number;
    show_in_statistics: boolean;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/phase'
      : '/api/admin/contact/phase/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Fáze procesu byla úspěšně vytvořena.'
            : 'Fáze procesu byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/kontakty/faze/' + response.id);
      } else if (redirect) {
        router.push('/kontakty/faze');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit fázi procesu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="contacts"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
        <LayoutContainer class="col-span-9 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormColorPicker
              v-model="item.color"
              label="Barva"
              name="color"
              class="col-span-1"
            />
            <BaseFormCheckbox
              v-model="item.show_in_statistics"
              :checked="item.show_in_statistics"
              label="Zobrazovat ve statistikách"
              name="show_in_statistics"
            />
          </div>
        </LayoutContainer>
        <LayoutActionsDetailBlock
          v-model:position="item.position"
          :allow-image="false"
          :allow-sites="false"
          :allow-position="true"
          :allow-translations="false"
          class="col-span-3"
        />
      </div>
    </Form>
  </div>
</template>
