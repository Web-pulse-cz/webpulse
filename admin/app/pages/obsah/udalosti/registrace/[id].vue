<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { useCountryStore } from '~/../stores/countryStore';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const countryStore = useCountryStore();
const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová registrace' : 'Detail registrace');

const breadcrumbs = ref([
  {
    name: 'Události a akce',
    link: '/obsah/udalosti',
    current: false,
  },
  {
    name: 'Registrace',
    link: '/obsah/udalosti/registrace',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/udalosti/registrace/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  event_id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  email: '' as string,
  phone: '' as string,
  note: '' as string,
  ico: '' as string,
  dic: '' as string,
  company: '' as string,
  street: '' as string,
  city: '' as string,
  zip: '' as string,
  country_id: 1 as number,
  is_paid: false as boolean,
  event: {
    id: null as number | null,
    name: '' as string,
  },
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    event_id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    note: string;
    ico: string;
    dic: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    country_id: number;
    is_paid: boolean;
    event: {
      id: number | null;
      name: string;
    };
  }>('/api/admin/event/registration/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = `Událost ${item.value.event.name} ─ ${item.value.firstname} ${item.value.lastname}`;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/udalosti/registrace/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst registraci. Zkuste to prosím později.',
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
    event_id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    note: string;
    ico: string;
    dic: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    country_id: number;
    is_paid: boolean;
    event: {
      id: number | null;
      name: string;
    };
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/event/registration'
      : '/api/admin/event/registration/' + route.params.id,
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
            ? 'Registrace byla úspěšně vytvořena.'
            : 'Registrace byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/udalosti/registrace/' + response.id);
      } else if (redirect) {
        router.push('/obsah/udalosti/registrace');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit registraci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="events"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-full w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.firstname"
              name="firstname"
              label="Jméno"
              type="text"
              rules="required|min:3"
            />
            <BaseFormInput v-model="item.ico" name="ico" label="IČO" type="text" />
            <BaseFormInput
              v-model="item.lastname"
              name="lastname"
              label="Příjmení"
              type="text"
              rules="required|min:3"
            />
            <BaseFormInput v-model="item.dic" name="dic" label="DIČ" type="text" />
            <BaseFormInput
              v-model="item.email"
              name="email"
              label="E-mail"
              type="text"
              rules="required|min:3"
            />
            <BaseFormInput v-model="item.company" name="company" label="Společnost" type="text" />
            <BaseFormInput v-model="item.phone" name="phone" label="Telefon" type="text" />
            <BaseFormInput
              v-model="item.street"
              name="street"
              label="Ulice a číslo popisné"
              type="text"
            />
            <BaseFormCheckbox v-model="item.is_paid" name="is_paid" label="Je uhrazeno?" />
            <BaseFormInput v-model="item.city" name="city" label="Město" type="text" />
            <div>&nbsp;</div>
            <BaseFormInput v-model="item.zip" name="zip" label="PSČ" type="text" />
            <div>&nbsp;</div>
            <BaseFormSelect
              v-model="item.country_id"
              name="country_id"
              label="Země"
              :options="countryStore.countriesOptions"
            />
            <BaseFormTextarea
              v-model="item.note"
              name="note"
              label="Poznámka"
              class="col-span-full"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
