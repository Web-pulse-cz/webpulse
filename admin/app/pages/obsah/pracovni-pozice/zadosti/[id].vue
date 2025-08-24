<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref('Detail žádosti');

const breadcrumbs = ref([
  {
    name: 'Pracovní pozice',
    link: '/obsah/pracovni-pozice',
    current: false,
  },
  {
    name: 'Žádosti',
    link: '/obsah/pracovni-pozice/zadosti',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/pracovni-pozice/zadosti/' + route.params.id,
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  email: '' as string,
  phone: '' as string,
  cover_letter: '' as string,
  resume: '' as string,
  status: 'pending' as string,
  salary_expectation: null as number | null,
  availability: null as string | null,
  source: '' as string,
  locale: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    cover_letter: string;
    resume: string;
    status: string;
    salary_expectation: number | null;
    availability: string | null;
    source: string;
    locale: string;
  }>('/api/admin/career/application/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = `Žádost o pracovní pozici ${item.value.career.name}`;
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst žádost. Zkuste to prosím později.',
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
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    cover_letter: string;
    resume: string;
    status: string;
    salary_expectation: number | null;
    availability: string | null;
    source: string;
    locale: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/career/application'
      : '/api/admin/career/application/' + route.params.id,
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
            ? 'Žádost byla úspěšně vytvořena.'
            : 'Žádost byla úspěšně upravena.',
        color: 'green',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/pracovni-pozice/zadosti/' + response.id);
      } else if (redirect) {
        router.push('/obsah/pracovni-pozice/zadosti');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit žádost. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="careers"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.firstname"
              name="firstname"
              label="Jméno"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.lastname"
              name="lastname"
              label="Příjmení"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.email"
              name="email"
              label="E-mail"
              type="email"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.phone"
              name="phone"
              label="Telefon"
              type="text"
              class="col-span-1"
            />
            <BaseFormTextarea
              v-model="item.cover_letter"
              name="cover_letter"
              label="Motivační dopis"
              class="col-span-2"
            />
            <BaseFormInput
              v-model="item.salary_expectation"
              name="salary_expectation"
              label="Očekávaný plat"
              type="number"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.availability"
              name="availability"
              label="Dostupnost"
              type="text"
              disabled
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full">
          <div class="grid grid-cols-1 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.locale"
              name="locale"
              label="Jazyk"
              type="text"
              :disabled="true"
              class="col-span-1"
            />
            <BaseFormSelect
              v-model="item.status"
              name="status"
              label="Stav žádosti"
              :options="[
                { value: 'pending', name: 'Čeká na vyřízení' },
                { value: 'reviewed', name: 'Zobrazeno' },
                { value: 'accepted', name: 'Přijato' },
                { value: 'rejected', name: 'Odmítnuto' },
              ]"
              class="col-span-1"
            />
            <BaseFormSelect
              v-model="item.source"
              name="source"
              label="Zdroj"
              :options="[
                { value: 'website', name: 'Webová stránka' },
                { value: 'referral', name: 'Odkaz' },
                { value: 'social_media', name: 'Sociální sítě' },
                { value: 'job_board', name: 'Portál nabídek' },
                { value: 'other', name: 'Jiné' },
              ]"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
