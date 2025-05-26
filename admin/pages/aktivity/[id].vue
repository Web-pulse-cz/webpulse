<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useActivityStore } from '~/stores/activityStore';

const activityStore = useActivityStore();

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová aktivita' : 'Detail aktivity');

const breadcrumbs = ref([
  {
    name: 'Aktivity',
    link: '/aktivity',
    current: false,
  },
  {
    name: 'Nová aktivita',
    link: '/aktivity/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string | null,
  description: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string | null;
    description: string | null;
  }>('/api/admin/activity/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/aktivity/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst aktivitu. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string | null;
    description: string | null;
  }>(
    route.params.id === 'pridat' ? '/api/admin/activity' : '/api/admin/activity/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Aktivita byla úspěšně vytvořena.'
            : 'Aktivita byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/aktivity');
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} aktivitu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      activityStore.fetchActivities();
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
      :title="route.params.id === 'pridat' ? 'Nová aktivita' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="activities"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <LayoutContainer>
        <div class="grid grid-cols-4 gap-x-8 gap-y-4">
          <BaseFormInput
            v-model="item.name"
            label="Název"
            type="text"
            name="name"
            rules="required|min:3"
            class="col-span-2"
          />
          <BaseFormColorPicker
            v-model="item.color"
            label="Barva"
            name="color"
            rules="min:3"
            class="col-span-2 lg:col-span-1"
          />
          <BaseFormTextarea
            v-model="item.description"
            label="Popisek"
            name="description"
            rules="min:3"
            class="col-span-full"
          />
        </div>
      </LayoutContainer>
    </Form>
  </div>
</template>
