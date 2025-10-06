<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const contacts = ref([] as Array<any>);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nový seznam kontaktů' : 'Detail seznamu kontaktů',
);

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Seznam kontaktů',
    link: '/kontakty/seznamy',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/kontakty/seznamy/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string;
  }>('/api/admin/contact/list/' + route.params.id, {
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
        link: '/seznamy/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst seznam kontaktů. Zkuste to prosím později.',
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
    color: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/list'
      : '/api/admin/contact/list/' + route.params.id,
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
          route.params.id === 'pridat'
            ? 'Seznam kontaktů byl úspěšně vytvořen.'
            : 'Seznam kontaktů byl úspěšně upraven.',
        color: 'green',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/kontakty/seznamy/' + response.id);
      } else if (redirect) {
        router.push('/kontakty/seznamy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit seznam kontaktů. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadContacts() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/contact?contact_list_id=' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      contacts.value.data = response;
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
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
    loadContacts();
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
    <Form v-if="route.params.id === 'pridat'" @submit="saveItem">
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
            <BaseFormColorPicker
              v-model="item.color"
              label="Barva"
              name="color"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
    <LayoutContainer v-else>
      <BaseTable
        :items="contacts"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
          {
            key: 'firstname',
            name: 'Jméno',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: false,
          },
          {
            key: 'lastname',
            name: 'Příjmení',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: false,
          },
          { key: 'phone', name: 'Telefon', type: 'text', width: 80, hidden: true, sortable: true },
          { key: 'email', name: 'E-mail', type: 'text', width: 80, hidden: true, sortable: true },
          {
            key: 'phase',
            name: 'Fáze',
            type: 'badge',
            width: 80,
            hidden: true,
            sortable: false,
            colorKey: 'phase_color',
          },
          {
            key: 'source',
            name: 'Zdroj',
            type: 'badge',
            width: 80,
            hidden: true,
            sortable: false,
            colorKey: 'source_color',
          },
        ]"
        :actions="[{ type: 'edit', hash: '#info', path: '/kontakty' }]"
        :loading="loading"
        :error="error"
        singular="Kontakt"
        plural="Kontakty"
        slug="contacts"
      />
    </LayoutContainer>
  </div>
</template>
