<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const phases = ref([]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová úkol' : 'Detail úkolu');

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Úkoly',
    link: '/ukoly',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/ukoly/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  contact_phase_id: null as string | null,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    contact_phase_id: string | null;
  }>('/api/admin/contact/task/' + route.params.id, {
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
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst úkol. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadPhases() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/phase', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      response.forEach((phase: { id: number; name: string }) => {
        phases.value.push({
          value: phase.id,
          name: phase.name,
        });
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst fáze. Zkuste to prosím později.',
        color: 'red',
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
    contact_phase_id: string | null;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/task'
      : '/api/admin/contact/task/' + route.params.id,
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
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat' ? 'Úkol byl úspěšně vytvořen.' : 'Úkol byl úspěšně upraven.',
        color: 'green',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/kontakty/ukoly/${response.id}`);
      } else if (redirect) {
        router.push('/kontakty/ukoly');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit úkol. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
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
  loadPhases();
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
      <div class="grid grid-cols-1 gap-x-10">
        <LayoutContainer class="col-span-full w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormSelect
              v-model="item.contact_phase_id"
              :options="phases"
              label="Fáze procesu"
              name="contact_phase_id"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
